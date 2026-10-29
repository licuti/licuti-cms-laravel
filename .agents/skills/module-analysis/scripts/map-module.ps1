<#
.SYNOPSIS
  Map the files of one Licuti CMS module by architecture layer (ASCII-only for PS 5.1 safety).

.EXAMPLE
  powershell -File .agents/skills/module-analysis/scripts/map-module.ps1 -Name FlashSale
  powershell -File .agents/skills/module-analysis/scripts/map-module.ps1 -Name ProductAttribute
#>
param(
    [Parameter(Mandatory = $true)][string]$Name,
    [string]$Root = (Get-Location).Path
)

# Name variants: PascalCase, kebab-case, snake_case
$pascal  = $Name
$kebab   = ($pascal -creplace '(?<!^)([A-Z])', '-$1').ToLower()
$snake   = ($pascal -creplace '(?<!^)([A-Z])', '_$1').ToLower()
$needles = @($pascal, $kebab, $snake) | Select-Object -Unique

function Test-Match([string]$Path) {
    foreach ($n in $needles) { if ($Path -like "*$n*") { return $true } }
    return $false
}

function Get-Rel([string]$Full) { return $Full.Substring($Root.Length).TrimStart('\', '/') }

Write-Host "== Module: $pascal  (kebab: $kebab | snake: $snake) ==" -ForegroundColor Cyan

function Find-ByName {
    param([string]$Label, [string]$RelPath, [string]$Filter = '*.php')
    $dir = Join-Path $Root $RelPath
    if (-not (Test-Path $dir)) { return }
    $files = Get-ChildItem -Path $dir -Recurse -File -Filter $Filter -ErrorAction SilentlyContinue |
        Where-Object { Test-Match $_.FullName }
    Write-Host ""
    Write-Host "[$Label]" -ForegroundColor Yellow
    if (-not $files) {
        Write-Host "  (none) <- worth noting if this layer should exist" -ForegroundColor DarkGray
        return
    }
    foreach ($f in $files) {
        $count = (Get-Content $f.FullName -ErrorAction SilentlyContinue | Measure-Object -Line).Lines
        Write-Host ("{0,5} lines  {1}" -f $count, (Get-Rel $f.FullName))
    }
}

Find-ByName 'Migration'      'database\migrations'
Find-ByName 'Model'          'app\Models'
Find-ByName 'Enum'           'app\Enums'
Find-ByName 'DTO'            'app\DTOs'
Find-ByName 'Repository'     'app\Repositories'
Find-ByName 'Service'        'app\Services'
Find-ByName 'Action'         'app\Actions'
Find-ByName 'Controller'     'app\Http\Controllers'
Find-ByName 'FormRequest'    'app\Http\Requests'
Find-ByName 'Observer'       'app\Observers'
Find-ByName 'Listener'       'app\Listeners'
Find-ByName 'Job'            'app\Jobs'
Find-ByName 'Console'        'app\Console'
Find-ByName 'Exception'      'app\Exceptions'
Find-ByName 'View'           'resources\views' '*.blade.php'
Find-ByName 'Test'           'tests'
Find-ByName 'Docs'           'docs' '*.md'

# Routes
Write-Host ""
Write-Host "[Routes]" -ForegroundColor Yellow
$pattern = ($needles | ForEach-Object { [regex]::Escape($_) }) -join '|'
$routeFiles = Get-ChildItem -Path (Join-Path $Root 'routes') -File -Filter *.php -ErrorAction SilentlyContinue
$routeHits = @()
foreach ($rf in $routeFiles) {
    $hits = Select-String -Path $rf.FullName -Pattern $pattern -ErrorAction SilentlyContinue
    foreach ($h in $hits) { $routeHits += ("  {0}:{1}  {2}" -f $rf.Name, $h.LineNumber, $h.Line.Trim()) }
}
if ($routeHits.Count -gt 0) { $routeHits | Write-Host } else { Write-Host "  (none)" -ForegroundColor DarkGray }

# Coupling: files OUTSIDE the module that mention the class name
Write-Host ""
Write-Host "[Coupling - files outside the module that mention '$pascal']" -ForegroundColor Yellow
$appDir = Join-Path $Root 'app'
$outside = Get-ChildItem -Path $appDir -Recurse -File -Filter *.php -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -notlike "*$pascal*" } |
    Select-String -Pattern ([regex]::Escape($pascal)) -List -ErrorAction SilentlyContinue
if ($outside) {
    foreach ($o in $outside) { Write-Host ("  " + (Get-Rel $o.Path)) }
} else {
    Write-Host "  (none - module is isolated or not integrated yet)" -ForegroundColor DarkGray
}

# Concurrency hot spots inside module files
Write-Host ""
Write-Host "[Concurrency hot spots in module files: transaction / lock / increment / afterCommit]" -ForegroundColor Yellow
$hotPattern = 'lockForUpdate|sharedLock|DB::transaction|handleTransaction|->increment\(|->decrement\(|afterCommit'
$hot = Get-ChildItem -Path $appDir -Recurse -File -Filter *.php -ErrorAction SilentlyContinue |
    Where-Object { Test-Match $_.FullName } |
    Select-String -Pattern $hotPattern -ErrorAction SilentlyContinue
if ($hot) {
    foreach ($h in $hot) { Write-Host ("  {0}:{1}  {2}" -f (Get-Rel $h.Path), $h.LineNumber, $h.Line.Trim()) }
} else {
    Write-Host "  (none)" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "Hint: read Migration -> Enum -> Model -> Service/Action -> Controller -> Test." -ForegroundColor Cyan
