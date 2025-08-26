Param(
  [string]$Base = "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1",
  [string]$Token = ""
)

# HTTP-only write verification for Subjects and TuitionYear endpoints
# Safe-by-design: creates temporary test records and cleans them up.
# Requires: Laravel API running and DB pointing to DEV data.
# Usage:
#   pwsh -File .\laravel-api\tests\scripts\http-write.ps1 -Base "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1"

$ErrorActionPreference = "Stop"

# Prepare output directory
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RepoRoot = Resolve-Path (Join-Path $ScriptDir "..\..")
$OutDir   = Join-Path $RepoRoot "tests\out"
New-Item -ItemType Directory -Path $OutDir -Force | Out-Null

# Common headers
$Headers = @{
  "Accept"      = "application/json"
  "Content-Type"= "application/json"
}
if ($Token -and $Token.Trim().Length -gt 0) {
  $Headers["Authorization"] = "Bearer $Token"
}

function Save-Response {
  param(
    [string]$Name,
    [int]$StatusCode,
    [string]$Content
  )
  $ts = Get-Date -Format "yyyyMMdd-HHmmss"
  $file = Join-Path $OutDir "$($ts)-$($Name).json"
  $wrapped = @{
    status = $StatusCode
    ts     = (Get-Date).ToString("o")
    body   = $null
  }
  try {
    $wrapped.body = ($Content | ConvertFrom-Json) | ConvertTo-Json -Depth 100 -Compress:$false
  } catch {
    $wrapped.body = $Content
  }
  $json = $wrapped | ConvertTo-Json -Depth 100 -Compress:$false
  Set-Content -Path $file -Value $json -Encoding UTF8
  return $file
}

function Invoke-POST {
  param([string]$Path, [hashtable]$Body, [string]$Name)
  $url = "$Base$Path"
  $jsonBody = ($Body | ConvertTo-Json -Depth 100)
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Method POST -Uri $url -Headers $Headers -Body $jsonBody -TimeoutSec 60
    $file = Save-Response -Name $Name -StatusCode $resp.StatusCode -Content $resp.Content
    return @{ ok = ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 300); status = $resp.StatusCode; file = $file; raw = $resp.Content }
  } catch {
    $status = if ($_.Exception.Response) { $_.Exception.Response.StatusCode.value__ } else { 0 }
    $content = if ($_.Exception.Response) {
      try { (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch { "" }
    } else { "" }
    $file = Save-Response -Name $Name -StatusCode $status -Content $content
    return @{ ok = $false; status = $status; file = $file; raw = $content }
  }
}

function Invoke-GET {
  param([string]$Path, [string]$Name)
  $url = "$Base$Path"
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Method GET -Uri $url -Headers $Headers -TimeoutSec 60
    $file = Save-Response -Name $Name -StatusCode $resp.StatusCode -Content $resp.Content
    return @{ ok = ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 300); status = $resp.StatusCode; file = $file; raw = $resp.Content }
  } catch {
    $status = if ($_.Exception.Response) { $_.Exception.Response.StatusCode.value__ } else { 0 }
    $content = if ($_.Exception.Response) {
      try { (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch { "" }
    } else { "" }
    $file = Save-Response -Name $Name -StatusCode $status -Content $content
    return @{ ok = $false; status = $status; file = $file; raw = $content }
  }
}

$results = @()
$suffix = Get-Date -Format "yyyyMMddHHmmss"
$subjectCode = "ZZTEST-SUBJ-$suffix"
$tuitionYearLabel = "ZZTEST-$suffix"

Write-Host "==== Subjects write flow ====" -ForegroundColor Cyan

# 1) Create subject
$createBody = @{
  strCode        = $subjectCode
  strDescription = "Test Subject $suffix"
  strUnits       = "3"
  intLab         = 0
  strDepartment  = "TEST"
  intMajor       = 0
  intLectHours   = 0
  strLabClassification = "none"
}
$r1 = Invoke-POST -Path "/subjects/submit" -Body $createBody -Name "httpw-subject-create"
$results += @{ name="subject-create"; res=$r1 }
if (-not $r1.ok) { Write-Host "Create subject failed: $($r1.status)" -ForegroundColor Red }

# 2) Resolve subject id from create response (preferred), fallback to lookup
$createdSubjectId = $null
if ($r1.ok) {
  try {
    $j = ($r1.raw | ConvertFrom-Json)
    if ($j.success -and $j.newid) { $createdSubjectId = [int]$j.newid }
  } catch { }
}

if (-not $createdSubjectId) {
  $r2 = Invoke-GET -Path ("/subjects?search=" + [uri]::EscapeDataString($subjectCode)) -Name "httpw-subject-lookup"
  $results += @{ name="subject-lookup"; res=$r2 }
  if ($r2.ok) {
    try {
      $data = ($r2.raw | ConvertFrom-Json)
      $item = $data.data | Where-Object { $_.strCode -eq $subjectCode } | Select-Object -First 1
      if ($item) { $createdSubjectId = [int]$item.intID }
    } catch { }
  }
  if (-not $createdSubjectId) {
    Write-Host "Could not resolve created subject ID. Subsequent subject steps will be skipped." -ForegroundColor Yellow
  }
}

# 3) Edit subject (if id known)
if ($createdSubjectId) {
  $editBody = @{
    intID         = $createdSubjectId
    strDescription= "Test Subject Updated $suffix"
  }
  $r3 = Invoke-POST -Path "/subjects/edit" -Body $editBody -Name "httpw-subject-edit"
  $results += @{ name="subject-edit"; res=$r3 }
  if (-not $r3.ok) { Write-Host "Edit subject failed: $($r3.status)" -ForegroundColor Red }

  # 4) Submit days (safe overwrite behavior)
  $daysBody = @{
    intSubjectID = $createdSubjectId
    subj         = @("1 3","2 4")
  }
  $r4 = Invoke-POST -Path "/subjects/submit-days" -Body $daysBody -Name "httpw-subject-days"
  $results += @{ name="subject-days"; res=$r4 }
  if (-not $r4.ok) { Write-Host "Submit days failed: $($r4.status)" -ForegroundColor Red }
}

# 5) Delete subject to cleanup (if id known)
if ($createdSubjectId) {
  $delBody = @{ id = $createdSubjectId }
  $r5 = Invoke-POST -Path "/subjects/delete" -Body $delBody -Name "httpw-subject-delete"
  $results += @{ name="subject-delete"; res=$r5 }
  if (-not $r5.ok) { Write-Host "Delete subject failed: $($r5.status)" -ForegroundColor Red }
}

Write-Host "==== TuitionYear write flow ====" -ForegroundColor Cyan

# 6) Add tuition year
$addTyBody = @{
  year        = $tuitionYearLabel
  isDefault   = 0
}
$ty1 = Invoke-POST -Path "/tuition-years/add" -Body $addTyBody -Name "httpw-ty-add"
$results += @{ name="ty-add"; res=$ty1 }
$newTyId = $null
if ($ty1.ok) {
  try {
    $ty1json = ($ty1.raw | ConvertFrom-Json)
    if ($ty1json.success -and $ty1json.newid) { $newTyId = [int]$ty1json.newid }
  } catch { }
}
if (-not $newTyId) {
  Write-Host "Could not resolve new tuition year ID from add response." -ForegroundColor Yellow
}

# 7) Duplicate tuition year (if id known)
$newTyDupId = $null
if ($newTyId) {
  $dupBody = @{ id = $newTyId }
  $ty2 = Invoke-POST -Path "/tuition-years/duplicate" -Body $dupBody -Name "httpw-ty-duplicate"
  $results += @{ name="ty-duplicate"; res=$ty2 }
  if ($ty2.ok) {
    try {
      $j = ($ty2.raw | ConvertFrom-Json)
      if ($j.success -and $j.newid) { $newTyDupId = [int]$j.newid }
    } catch { }
  }
}

# 8) Cleanup: delete duplicate first then original
if ($newTyDupId) {
  $tyDelDup = Invoke-POST -Path "/tuition-years/delete" -Body @{ id = $newTyDupId } -Name "httpw-ty-delete-dup"
  $results += @{ name="ty-delete-dup"; res=$tyDelDup }
}
if ($newTyId) {
  $tyDel = Invoke-POST -Path "/tuition-years/delete" -Body @{ id = $newTyId } -Name "httpw-ty-delete"
  $results += @{ name="ty-delete"; res=$tyDel }
}

# Summary
$pass = ($results | Where-Object { $_.res.ok }).Count
$total = $results.Count
$fail  = $total - $pass

$summary = @{
  startedAt = (Get-Date).ToString("o")
  base      = $Base
  total     = $total
  passed    = $pass
  failed    = $fail
  details   = $results | ForEach-Object {
    @{
      name   = $_.name
      ok     = $_.res.ok
      status = $_.res.status
      file   = $_.res.file
    }
  }
} | ConvertTo-Json -Depth 10

$summaryFile = Save-Response -Name "httpw-summary" -StatusCode 200 -Content $summary
Write-Host "HTTP write test results: $pass/$total passed. Summary saved: $summaryFile" -ForegroundColor Green

if ($fail -gt 0) { exit 1 } else { exit 0 }
