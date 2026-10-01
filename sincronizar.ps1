param(
    [string]$Mensagem = "Atualiza projeto"
)

$ErrorActionPreference = 'Stop'
$git = Get-Command git -ErrorAction SilentlyContinue

if (-not $git) {
    $gitPath = 'C:\Program Files\Git\cmd\git.exe'
    if (Test-Path $gitPath) {
        $git = Get-Item $gitPath
    } else {
        throw 'Git não encontrado. Instale o Git for Windows antes de sincronizar.'
    }
}

& $git.Source add .
$status = @(& $git.Source status --short)

if ($status.Count -eq 0) {
    Write-Host 'Nenhuma alteração para enviar.'
    exit 0
}

& $git.Source commit -m $Mensagem
& $git.Source push
Write-Host 'Projeto sincronizado com o GitHub.'