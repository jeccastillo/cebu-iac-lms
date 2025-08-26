param(
  [Parameter(Mandatory = $true)]
  [string]$Base
)

$ErrorActionPreference = 'Stop'
$results = @()
$now = Get-Date -Format 'yyyyMMdd-HHmmss'
$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$outDir = Join-Path (Split-Path -Parent $scriptRoot) 'out'
if (-not (Test-Path $outDir)) {
  New-Item -ItemType Directory -Path $outDir | Out-Null
}
$outFile = Join-Path $outDir "$now-curriculum-smoke.json"

function Add-Result {
  param([string]$Name, [bool]$Passed, [string]$Details)
  $script:results += [pscustomobject]@{
    name    = $Name
    passed  = $Passed
    details = $Details
  }
}

function Invoke-Api {
  param(
    [Parameter(Mandatory = $true)][ValidateSet('GET','POST','PUT','DELETE')] [string]$Method,
    [Parameter(Mandatory = $true)][string]$Url,
    [Parameter(Mandatory = $false)][hashtable]$Body
  )
  try {
    $params = @{
      Method  = $Method
      Uri     = $Url
      Headers = @{ 'Accept' = 'application/json' }
    }
    if ($Body) {
      $json = ($Body | ConvertTo-Json -Depth 6)
      $params['Body'] = $json
      $params['ContentType'] = 'application/json'
    }
    $resp = Invoke-RestMethod @params
    return @{ ok = $true; status = 200; body = $resp }
  } catch {
    $ex = $_.Exception
    $resp = $null
    $status = $null
    if ($_.Exception.Response -ne $null) {
      try {
        $status = $_.Exception.Response.StatusCode.Value__
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $text = $reader.ReadToEnd()
        if ($text) {
          try { $resp = $text | ConvertFrom-Json } catch { $resp = @{ raw = $text } }
        }
      } catch {}
    }
    return @{ ok = $false; status = $status; body = $resp; error = $ex.Message }
  }
}

Write-Host "==== Curriculum Critical-Path Smoke ===="

# A0: fetch a program id to use for curriculum creation
# /programs returns items shaped as: { id, title, type, strMajor }
$programsUrl = "$Base/programs?enabledOnly=true"
$prog = Invoke-Api -Method GET -Url $programsUrl
if (-not $prog.ok) {
  Add-Result "Programs GET" $false ("HTTP {0} - {1}" -f $prog.status, $prog.error)
  throw "Cannot proceed without a program id"
}
try {
  $first = $prog.body.data[0]
  if (-not $first) {
    # handle object instead of array
    $first = $prog.body.data
  }
  $programId = $first.id
  if (-not $programId) { throw "No program id in response (expected 'id' field from /programs)" }
  Add-Result "Programs GET" $true "ProgramID=$programId"
} catch {
  Add-Result "Programs GET" $false "Parsing failure: $($_.Exception.Message)"
  throw
}

# A1: GET /curriculum
$idx = Invoke-Api -Method GET -Url ($Base + "/curriculum?limit=1")
if ($idx.ok -and $idx.body.success -eq $true -and $idx.body.meta.limit -ge 1) {
  Add-Result "Curriculum index" $true "OK"
} else {
  Add-Result "Curriculum index" $false ("Unexpected response: {0}" -f ($idx | ConvertTo-Json -Depth 6))
}

# A4: POST /curriculum
$nameCreate = "API Test Curriculum $now"
$create = Invoke-Api -Method POST -Url ($Base + "/curriculum") -Body @{
  strName      = $nameCreate
  intProgramID = [int]$programId
  active       = 1
  isEnhanced   = 0
}
$newId = $null
if ($create.ok -and ($create.status -eq 200 -or $create.status -eq 201) -and $create.body.success -eq $true) {
  $newId = [int]$create.body.newid
  if ($newId -gt 0 -and $create.body.data.strName -eq $nameCreate) {
    Add-Result "Curriculum create" $true ("newid=$newId")
  } else {
    Add-Result "Curriculum create" $false ("Missing/invalid newid or data: {0}" -f ($create.body | ConvertTo-Json -Depth 6))
    throw "Create failed"
  }
} else {
  Add-Result "Curriculum create" $false ("HTTP {0} body: {1}" -f $create.status, ($create.body | ConvertTo-Json -Depth 6))
  throw "Create failed"
}

# A2: GET /curriculum/{id}
$show = Invoke-Api -Method GET -Url ($Base + "/curriculum/$newId")
if ($show.ok -and $show.body.success -eq $true -and $show.body.data.intID -eq $newId) {
  Add-Result "Curriculum show" $true "OK"
} else {
  Add-Result "Curriculum show" $false ("Unexpected: {0}" -f ($show | ConvertTo-Json -Depth 6))
}

# A5: PUT /curriculum/{id}
$nameUpdate = "$nameCreate (updated)"
$upd = Invoke-Api -Method PUT -Url ($Base + "/curriculum/$newId") -Body @{ strName = $nameUpdate }
if ($upd.ok -and $upd.body.success -eq $true -and $upd.body.data.strName -eq $nameUpdate) {
  Add-Result "Curriculum update" $true "OK"
} else {
  Add-Result "Curriculum update" $false ("Unexpected: {0}" -f ($upd | ConvertTo-Json -Depth 6))
}

# A3: GET /curriculum/{id}/subjects
$subs = Invoke-Api -Method GET -Url ($Base + "/curriculum/$newId/subjects")
if ($subs.ok -and $subs.body.success -eq $true -and ($subs.body.data -is [System.Array])) {
  Add-Result "Curriculum subjects" $true ("count={0}" -f $subs.body.data.Count)
} else {
  Add-Result "Curriculum subjects" $false ("Unexpected: {0}" -f ($subs | ConvertTo-Json -Depth 6))
}

# Cleanup: DELETE /curriculum/{id}
$del = Invoke-Api -Method DELETE -Url ($Base + "/curriculum/$newId")
if ($del.ok -and $del.body.success -eq $true) {
  Add-Result "Curriculum delete" $true "OK"
} else {
  Add-Result "Curriculum delete" $false ("HTTP {0} body: {1}" -f $del.status, ($del.body | ConvertTo-Json -Depth 6))
}

# Confirm 404 after delete
$show404 = Invoke-Api -Method GET -Url ($Base + "/curriculum/$newId")
if (-not $show404.ok -and $show404.status -eq 404) {
  Add-Result "Curriculum show after delete (404)" $true "OK"
} else {
  Add-Result "Curriculum show after delete (404)" $false ("Unexpected: {0}" -f ($show404 | ConvertTo-Json -Depth 6))
}

# Summary
$summary = [pscustomobject]@{
  base    = $Base
  time    = (Get-Date).ToString('s')
  results = $results
  passed  = ($results | Where-Object { -not $_.passed }).Count -eq 0
}

$summary | ConvertTo-Json -Depth 6 | Out-File -FilePath $outFile -Encoding UTF8
Write-Host "==== Summary ===="
Write-Host ("Saved: {0}" -f $outFile)
$passCount = ($results | Where-Object { $_.passed }).Count
$total = $results.Count
Write-Host ("Results: {0}/{1} passed" -f $passCount, $total)
$results | ForEach-Object {
  $status = if ($_.passed) { 'PASS' } else { 'FAIL' }
  Write-Host ("[{0}] {1} - {2}" -f $status, $_.name, $_.details)
}

if (-not $summary.passed) { exit 1 } else { exit 0 }
