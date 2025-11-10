<?php
function runBranch($branchId, $occurrences = 1500) {
    $command = "php cliente-filial.php {$branchId} {$occurrences}";
    
    if (PHP_OS_FAMILY === 'Windows') {
        pclose(popen("start /B {$command}", "r"));
    } else {
        exec("{$command} > /dev/null 2>&1 &");
    }
    
    echo "Filial {$branchId} iniciada\n";
}

// Simular 5 filiais
echo "Iniciando simulação com 5 filiais...\n\n";

for ($i = 1; $i <= 5; $i++) {
    $branchId = 'FILIAL' . str_pad($i, 2, '0', STR_PAD_LEFT);
    runBranch($branchId, 1500);
    usleep(500000); // 0.5 segundo entre inicializações
}

echo "\nTodas as filiais foram iniciadas.\n";
echo "Aguarde a finalização de todas as simulações.\n";