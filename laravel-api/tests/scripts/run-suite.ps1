Param(
  [string]$Base = "http://localhost/laravel-api/public/api/v1",
  [string]$Token = "",
  [switch]$RunWrites,
  [string]$PhpPath = "php"
)

# Comprehensive test suite runner for Laravel API v1
# Usage examples:
#   pwsh -File .\laravel-api\tests\scripts\run-suite.ps1
#   pwsh -File .\laravel-api\tests\scripts\run-suite.ps1 -Base "http://localhost/laravel-api/public/api/v1" -Token "YOUR_TOKEN"
#   pwsh -File .\laravel-api\tests\scripts\run-suite.ps1 -RunWrites
#
# Notes:
# - Always target a DEV database when using -RunWrites (write test scripts will mutate data).
# - This script orchestrates:
#     1) Smoke (read-only endpoints)
#     2) Optional write tests (Subjects & TuitionYear) using provided PHP scripts
# - Outputs artifacts into laravel-api/tests/out with timestamped filenames.
# - Summarizes run results to suite-summary-*.json

$ErrorActionPreference = "Stop"

# Paths and directories
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RepoRoot = Resolve-Path (Join-Path $ScriptDir "..\..")
$OutDir = Join-Path $RepoRoot "tests\out"
$SmokeScript = Join-Path $RepoRoot "tests\scripts\smoke.ps1"

New-Item -ItemType Directory -Path $OutDir -Force | Out-Null

function Save-Json {
  param(
    [Parameter(Mandatory=$true)][object]$Object,
    [Parameter(Mandatory=$true)][string]$Name
  )
  $ts = Get-Date -Format "yyyyMMdd-HHmmss"
  $file = Join-Path $OutDir "$($ts)-$($Name).json"
  $json = $Object | ConvertTo-Json -Depth 100 -Compress:$false
  Set-Content -Path $file -Value $json -Encoding UTF8
  return $file
}

function Run-Proc {
  param(
    [Parameter(Mandatory=$true)][string]$Name,
    [Parameter(Mandatory=$true)][string]$Command,
    [string[]]$Args = @()
  )
  $ts = Get-Date -Format "yyyyMMdd-HHmmss"
  $stdoutFile = Join-Path $OutDir "$($ts)-$($Name)-stdout.txt"
  $stderrFile = Join-Path $OutDir "$($ts)-$($Name)-stderr.txt"

  Write-Host "-> Running: $Name" -ForegroundColor Cyan
  Write-Host "   $Command $($Args -join ' ')"

  $psi = New-Object System.Diagnostics.ProcessStartInfo
  $psi.FileName = $Command
  $psi.RedirectStandardOutput = $true
  $psi.RedirectStandardError = $true
  $psi.UseShellExecute = $false
  $psi.Arguments = ($Args -join " ")

  $proc = New-Object System.Diagnostics.Process
  $proc.StartInfo = $psi
  [void]$proc.Start()

  $stdOut = $proc.StandardOutput.ReadToEnd()
  $stdErr = $proc.StandardError.ReadToEnd()
  $proc.WaitForExit()
  $code = $proc.ExitCode

  Set-Content -Path $stdoutFile -Value $stdOut -Encoding UTF8
  Set-Content -Path $stderrFile -Value $stdErr -Encoding UTF8

  return @{
    name = $Name
    exitCode = $code
    stdout = $stdoutFile
    stderr = $stderrFile
  }
}

$results = @()

# 1) Smoke (read-only)
try {
  $results += @{
    name = "smoke"
    step = "read-only"
    res = (Run-Proc -Name "smoke" -Command "powershell" -Args @("-NoProfile","-ExecutionPolicy","Bypass","-File","`"$SmokeScript`"","-Base","`"$Base`"","-Token","`"$Token`""))
  }
} catch {
  $results += @{
    name = "smoke"
    step = "read-only"
    res = @{ name = "smoke"; exitCode = -1; stdout = $null; stderr = $null }
    error = $_.Exception.Message
  }
}

# 2) Optional write tests (Subjects & TuitionYear)
if ($RunWrites.IsPresent) {
  Write-Host "Write tests enabled (-RunWrites). Ensure you are using a DEV database." -ForegroundColor Yellow

  $php = $PhpPath
  $subjectWrite = Join-Path $RepoRoot "scripts\test_subject_write.php"
  $subjectVerify = Join-Path $RepoRoot "scripts\verify_subject_writes.php"
  $tyWrite = Join-Path $RepoRoot "scripts\test_tuition_year_write.php"
  $tyExtra = Join-Path $RepoRoot "scripts\test_tuition_year_extra.php"

  # Subjects write
  foreach ($script in @($subjectWrite, $subjectVerify)) {
    if (Test-Path $script) {
      $results += @{
        name = "subjects-" + (Split-Path $script -Leaf)
        step = "write"
        res = (Run-Proc -Name ("php-" + (Split-Path $script -Leaf)) -Command $php -Args @("`"$script`""))
      }
    } else {
      $results += @{
        name = "subjects-" + (Split-Path $script -Leaf)
        step = "write"
        res = @{ name = "missing"; exitCode = -2; stdout = $null; stderr = $null }
        warning = "Script not found: $script"
      }
    }
  }

  # TuitionYear write
  foreach ($script in @($tyWrite, $tyExtra)) {
    if (Test-Path $script) {
      $results += @{
        name = "tuitionYear-" + (Split-Path $script -Leaf)
        step = "write"
        res = (Run-Proc -Name ("php-" + (Split-Path $script -Leaf)) -Command $php -Args @("`"$script`""))
      }
    } else {
      $results += @{
        name = "tuitionYear-" + (Split-Path $script -Leaf)
        step = "write"
        res = @{ name = "missing"; exitCode = -2; stdout = $null; stderr = $null }
        warning = "Script not found: $script"
      }
    }
  }
} else {
  Write-Host "Skipping write tests (Subjects/TuitionYear). Pass -RunWrites to enable." -ForegroundColor Yellow
}

# Summary
$summary = @{
  startedAt = (Get-Date).ToString("o")
  base = $Base
  runWrites = $RunWrites.IsPresent
  items = ($results | ForEach-Object {
    @{
      name = $_.name
      step = $_.step
      exitCode = $_.res.exitCode
      stdout = $_.res.stdout
      stderr = $_.res.stderr
      warning = $_.warning
      error = $_.error
    }
  })
}

$summaryFile = Save-Json -Object $summary -Name "suite-summary"
Write-Host "Suite summary saved: $summaryFile" -ForegroundColor Green

# Exit code: non-zero if any step failed
$failed = ($summary.items | Where-Object { $_.exitCode -ne 0 -and $_.exitCode -ne $null }).Count
if ($failed -gt 0) {
  exit 1
} else {
  exit 0
}
