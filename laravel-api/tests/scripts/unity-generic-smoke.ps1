Param(
  [string]$Base = "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1",
  [string]$OutDir = "laravel-api/tests/out"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

New-Item -ItemType Directory -Force -Path $OutDir | Out-Null
$ts = Get-Date -Format "yyyyMMdd-HHmmss"
$outFile = Join-Path $OutDir "unity-generic-smoke-$ts.json"

function Get-ResponseObject {
  Param(
    [Parameter(Mandatory=$true)][System.Net.WebResponse]$Response
  )
  try {
    $content = ""
    try {
      $stream = $Response.GetResponseStream()
      if ($null -ne $stream -and $stream.CanRead) {
        $sr = New-Object System.IO.StreamReader($stream)
        $content = $sr.ReadToEnd()
      }
    } catch {
      $content = ""
    }
    $status = 0
    if ($Response -is [System.Net.HttpWebResponse]) {
      $status = [int]$Response.StatusCode
    } elseif ($Response.PSObject.Properties.Name -contains 'StatusCode') {
      $status = [int]$Response.StatusCode
    } else {
      $status = 0
    }
    @{ status = $status; body = $content }
  } catch {
    @{ status = -1; body = $_.ToString() }
  }
}

function Invoke-JsonPost {
  Param(
    [Parameter(Mandatory=$true)][string]$Url,
    [Parameter(Mandatory=$true)]$Payload
  )
  try {
    $json = $Payload | ConvertTo-Json -Depth 8
    $resp = Invoke-WebRequest -Uri $Url -Method Post -ContentType "application/json" -Body $json -UseBasicParsing -ErrorAction Stop
    $status = 0
    if ($resp.PSObject.Properties.Name -contains 'StatusCode') {
      $status = [int]$resp.StatusCode
    }
    $body = ""
    if ($resp.PSObject.Properties.Name -contains 'Content' -and $resp.Content) {
      $body = $resp.Content
    }
    return @{ status = $status; body = $body }
  } catch {
    $ex = $_.Exception
    if ($ex -and $ex.Response) {
      return (Get-ResponseObject -Response $ex.Response)
    }
    return @{ status = -1; body = $_.ToString() }
  }
}

function Invoke-Get {
  Param(
    [Parameter(Mandatory=$true)][string]$Url
  )
  try {
    $resp = Invoke-WebRequest -Uri $Url -Method Get -UseBasicParsing -ErrorAction Stop
    $status = 0
    if ($resp.PSObject.Properties.Name -contains 'StatusCode') {
      $status = [int]$resp.StatusCode
    }
    $body = ""
    if ($resp.PSObject.Properties.Name -contains 'Content' -and $resp.Content) {
      $body = $resp.Content
    }
    return @{ status = $status; body = $body }
  } catch {
    $ex = $_.Exception
    if ($ex -and $ex.Response) {
      return (Get-ResponseObject -Response $ex.Response)
    }
    return @{ status = -1; body = $_.ToString() }
  }
}

$results = @()

Write-Host "== Testing POST /unity/advising (valid) =="
$r1 = Invoke-JsonPost -Url "$Base/unity/advising" -Payload @{
  student_number = "S-0001";
  program_id     = 1;
  term           = "1st Term 2024-2025";
  subjects       = @(@{ subject_id = 1 })
}
$results += @{ endpoint="/unity/advising"; case="valid"; result=$r1 }
Write-Host ("Status: {0}" -f $r1.status)

Write-Host "== Testing POST /unity/advising (422 expected: missing student_number) =="
$r2 = Invoke-JsonPost -Url "$Base/unity/advising" -Payload @{
  program_id     = 1;
  term           = "1st Term 2024-2025";
  subjects       = @(@{ subject_id = 1 })
}
$results += @{ endpoint="/unity/advising"; case="missing-student_number"; result=$r2 }
Write-Host ("Status: {0}" -f $r2.status)

Write-Host "== Testing POST /unity/tuition-preview (valid) =="
$r3 = Invoke-JsonPost -Url "$Base/unity/tuition-preview" -Payload @{
  student_number = "S-0001";
  program_id     = 1;
  term           = "1st Term 2024-2025";
  subjects       = @(@{ subject_id = 1 })
}
$results += @{ endpoint="/unity/tuition-preview"; case="valid"; result=$r3 }
Write-Host ("Status: {0}" -f $r3.status)

Write-Host "== Testing POST /unity/enlist (501 expected) =="
$r4 = Invoke-JsonPost -Url "$Base/unity/enlist" -Payload @{}
$results += @{ endpoint="/unity/enlist"; case="placeholder"; result=$r4 }
Write-Host ("Status: {0}" -f $r4.status)

Write-Host "== Testing POST /unity/tag-status (501 expected) =="
$r5 = Invoke-JsonPost -Url "$Base/unity/tag-status" -Payload @{}
$results += @{ endpoint="/unity/tag-status"; case="placeholder"; result=$r5 }
Write-Host ("Status: {0}" -f $r5.status)

Write-Host "== Testing GET /generic/faculty (no filter) =="
$r6 = Invoke-Get -Url "$Base/generic/faculty"
$results += @{ endpoint="/generic/faculty"; case="no-filter"; result=$r6 }
Write-Host ("Status: {0}" -f $r6.status)

Write-Host "== Testing GET /generic/faculty?q=John =="
$r7 = Invoke-Get -Url "$Base/generic/faculty?q=John"
$results += @{ endpoint="/generic/faculty"; case="q=John"; result=$r7 }
Write-Host ("Status: {0}" -f $r7.status)

Write-Host "== Regression: POST /student/viewer (token) =="
$r8 = Invoke-JsonPost -Url "$Base/student/viewer" -Payload @{ token = "test@example.com" }
$results += @{ endpoint="/student/viewer"; case="token"; result=$r8 }
Write-Host ("Status: {0}" -f $r8.status)

Write-Host "== Regression: POST /student/balances =="
$r9 = Invoke-JsonPost -Url "$Base/student/balances" -Payload @{ student_number = "S-0001" }
$results += @{ endpoint="/student/balances"; case="basic"; result=$r9 }
Write-Host ("Status: {0}" -f $r9.status)

Write-Host "== Regression: GET /registrar/grading/meta?dept=college =="
$r10 = Invoke-Get -Url "$Base/registrar/grading/meta?dept=college"
$results += @{ endpoint="/registrar/grading/meta"; case="college"; result=$r10 }
Write-Host ("Status: {0}" -f $r10.status)

Write-Host "== Regression: GET /finance/transactions?student_number=S-0001 =="
$r11 = Invoke-Get -Url "$Base/finance/transactions?student_number=S-0001"
$results += @{ endpoint="/finance/transactions"; case="student_number=S-0001"; result=$r11 }
Write-Host ("Status: {0}" -f $r11.status)

$payload = $results | ConvertTo-Json -Depth 8
$payload | Set-Content -LiteralPath $outFile -Encoding UTF8

Write-Host ("Saved results to {0}" -f $outFile)
