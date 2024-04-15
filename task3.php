<?php

require_once "ConsoleTable.php";

function hasParameters():bool {
    global $argv;
    $isValid = true;
    $parametersCount = count($argv) - 1;

    if ($parametersCount <= 1) {
        print_r("Haven't input parameters".PHP_EOL);
        $isValid = false;
    }

    if ($parametersCount < 3) {
        print_r("Needs more parameters".PHP_EOL);
        $isValid = false;
    }

    if($parametersCount % 2 === 0){
        print_r("Needs odd amount of parameters".PHP_EOL);
        $isValid = false;
    }

    return $isValid;
}

function hasUniqueParameters():bool {
    global $argv;
    $isValid = true;
    $valuesCount = array_slice(array_count_values($argv), 1);

    foreach ($valuesCount as $key => $value) {
        if ($value > 1) {
            print_r("Repeated paramater {$key} : {$value} times" . PHP_EOL);
            $isValid = false;
            break;
        }
    }

    return $isValid;
}

function alignParameters():array {
    global $argv;
    $parameters = array_slice($argv, 1);
    array_unshift($parameters, "");
    unset($parameters[0]);
    return $parameters;
}

function createMapBeats(array $parameters):array {
    $mapBeats = array();
    $lengthParameters = count($parameters);
    $arrayMiddle = intdiv($lengthParameters , 2);

    foreach ($parameters as $key => $value){
        $parametersCopy = $parameters;
        unset($parametersCopy[$key]);
        $parametersCopy = array_merge($parametersCopy);
        $i = 0;
        foreach($parametersCopy as $valueCopy) {
            if ($i < $arrayMiddle)
                $mapBeats[$value][$valueCopy] = "Win";
            else
                $mapBeats[$value][$valueCopy] = "Lose";
            ++$i;
        }
        $mapBeats[$value][$value] = "Draw";
    }

    return $mapBeats;
}

function clearScreen():void {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}
function printMenuChoice(array $parameters):void {
    print_r("Available Moves".PHP_EOL);
    foreach ($parameters as $key => $value){
        print_r("{$key} - {$value}".PHP_EOL);
    }
    print_r("0 - exit".PHP_EOL);
    print_r("? - help".PHP_EOL);
}

function printHelpTable(ConsoleTable $consoleTable, array $headers, array $mapBeats):void {

    $consoleTable->showAllBorders();
    $consoleTable->setIndent();
    $consoleTable->setHeaders($headers);

    unset($headers[0]);

    foreach ($headers as $row) {
        $rowLine = array();
        $rowLine[] = $row;
        foreach ($headers as $column){
            $rowLine[] = $mapBeats[$row][$column];
        }
        $consoleTable->addRow($rowLine);
    }
    $consoleTable->display();
}


function main(): void {
    if (!hasParameters() || !hasUniqueParameters())
        exit(-1);

    $parameters = alignParameters();

    $hashHmacAlgorithm = hash_hmac_algos()[11];
    $randomKey = random_bytes(32);
    $computerChoice = random_int(1,count($parameters));
    $hmacTurn = hash_hmac($hashHmacAlgorithm, strval($computerChoice), $randomKey);

    print("Computer choice : {$computerChoice}". PHP_EOL . "Hmac : {$hmacTurn}" . PHP_EOL);
    foreach ($parameters as $key => $value)
        print("{$key} : {$value}" . PHP_EOL);
    $playerChoice = readline("Player choice : ");
    $hmacTurn = hash_hmac($hashHmacAlgorithm, $playerChoice, $randomKey);
    print_r($hmacTurn.PHP_EOL);
    $mapBeats = createMapBeats($parameters);
    $consoleTable = new ConsoleTable();
    $parametersCopy = $parameters;
    array_unshift($parametersCopy, " ");
    printHelpTable($consoleTable, $parametersCopy, $mapBeats);
}

main();