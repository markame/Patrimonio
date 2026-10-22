$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$comandoPhp = Get-Command php -ErrorAction SilentlyContinue
if ($comandoPhp) {
    $executavelPhp = $comandoPhp.Source
} else {
    $pastaPhp = Get-ChildItem -LiteralPath 'C:\laragon\bin\php' -Directory | Sort-Object Name -Descending | Select-Object -First 1
    if (-not $pastaPhp) { throw 'PHP não encontrado. Execute pelo terminal do Laragon.' }
    $executavelPhp = Join-Path $pastaPhp.FullName 'php.exe'
}
Write-Host 'Escola Patrimônio: http://localhost:8000 (Ctrl+C para encerrar)'
& $executavelPhp -d upload_max_filesize=6M -d post_max_size=8M -S localhost:8000 -t public
