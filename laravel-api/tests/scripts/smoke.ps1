Param(
  [string]$Base = "http://localhost/laravel-api/public/api/v1",
  [string]$Token = ""
)

# Smoke test runner for Laravel API v1
# Usage:
#   pwsh -File .\laravel-api\tests\scripts\smoke.ps1 -Base "http://localhost/laravel-api/public/api/v1" -Token "YOUR_TOKEN"
# Notes:
# - This script exercises read-only endpoints by default.
# - Write endpoints are intentionally excluded from smoke to avoid DB mutations.
# - Outputs JSON responses into laravel-api/tests/out with timestamped filenames.

$ErrorActionPreference = "Stop"

# Prepare output directory
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RepoRoot = Resolve-Path (Join-Path $ScriptDir "..\..")
$OutDir = Join-Path $RepoRoot "tests\out"
New-Item -ItemType Directory -Path $OutDir -Force | Out-Null

# Common headers
$Headers = @{
  "Accept" = "application/json"
}
if ($Token -and $Token.Trim().Length -gt 0) {
  $Headers["Authorization"] = "Bearer $Token"
}

function Write-Section {
  param([string]$Name)
  Write-Host ""
  Write-Host "==== $Name ====" -ForegroundColor Cyan
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
    # Try to pretty print JSON if possible
    $wrapped.body = ($Content | ConvertFrom-Json) | ConvertTo-Json -Depth 100 -Compress:$false
  } catch {
    $wrapped.body = $Content
  }
  $json = $wrapped | ConvertTo-Json -Depth 100 -Compress:$false
  Set-Content -Path $file -Value $json -Encoding UTF8
  return $file
}

function Invoke-GET {
  param([string]$Path, [string]$Name)
  $url = "$Base$Path"
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Method GET -Uri $url -Headers $Headers -TimeoutSec 60
    $file = Save-Response -Name $Name -StatusCode $resp.StatusCode -Content $resp.Content
    return @{ ok = ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 300); status = $resp.StatusCode; file = $file }
  } catch {
    $status = if ($_.Exception.Response) { $_.Exception.Response.StatusCode.value__ } else { 0 }
    $content = if ($_.Exception.Response) {
      try { (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch { "" }
    } else { "" }
    $file = Save-Response -Name $Name -StatusCode $status -Content $content
    return @{ ok = $false; status = $status; file = $file }
  }
}

function Invoke-POST {
  param([string]$Path, [hashtable]$Body, [string]$Name)
  $url = "$Base$Path"
  $jsonBody = ($Body | ConvertTo-Json -Depth 100)
  $localHeaders = $Headers.Clone()
  $localHeaders["Content-Type"] = "application/json"
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Method POST -Uri $url -Headers $localHeaders -Body $jsonBody -TimeoutSec 60
    $file = Save-Response -Name $Name -StatusCode $resp.StatusCode -Content $resp.Content
    return @{ ok = ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 300); status = $resp.StatusCode; file = $file }
  } catch {
    $status = if ($_.Exception.Response) { $_.Exception.Response.StatusCode.value__ } else { 0 }
    $content = if ($_.Exception.Response) {
      try { (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch { "" }
    } else { "" }
    $file = Save-Response -Name $Name -StatusCode $status -Content $content
    return @{ ok = $false; status = $status; file = $file }
  }
}

$results = @()

Write-Section "Health"
$results += @{ name = "health";           res = (Invoke-GET -Path "/health" -Name "health") }

Write-Section "Programs"
$results += @{ name = "programs";         res = (Invoke-GET -Path "/programs" -Name "programs") }

Write-Section "Subjects (read)"
$results += @{ name = "subjects";         res = (Invoke-GET -Path "/subjects" -Name "subjects") }
$results += @{ name = "subjectsByCurr";   res = (Invoke-GET -Path "/subjects/by-curriculum?curriculum=1" -Name "subjects-by-curriculum") }

Write-Section "Tuition Year (read)"
$results += @{ name = "tuitionYears";     res = (Invoke-GET -Path "/tuition-years" -Name "tuition-years") }

Write-Section "Portal"
$results += @{ name = "portalActive";     res = (Invoke-GET -Path "/portal/active-programs" -Name "portal-active-programs") }

# Optional POST endpoints (read-like or non-mutating). Commented out by default.
# Uncomment and provide a real token or inputs to exercise these.
# Write-Section "Portal student-data (POST)"
# $results += @{ name = "portalStudentData"; res = (Invoke-POST -Path "/portal/student-data" -Body @{ token = "REPLACE_TOKEN" } -Name "portal-student-data") }

Write-Section "Summary"
$pass = ($results | Where-Object { $_.res.ok }).Count
$total = $results.Count
$fail = $total - $pass

$summary = @{
  total = $total
  passed = $pass
  failed = $fail
  details = $results | ForEach-Object {
    @{
      name = $_.name
      ok = $_.res.ok
      status = $_.res.status
      file = $_.res.file
    }
  }
} | ConvertTo-Json -Depth 10

$summaryFile = Save-Response -Name "summary" -StatusCode 200 -Content $summary
Write-Host "Results: $pass/$total passed. Summary saved: $summaryFile"

if ($fail -gt 0) {
  exit 1
} else {
  exit 0
}
