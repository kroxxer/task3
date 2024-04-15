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
            print_r("Repeated parameter {$key} : {$value} times" . PHP_EOL);
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

function addChoices(array &$parameters):void {
    $parameters["0"] = "exit";
    $parameters["?"] = "help";
}

function printMenuChoice(array $choices):void {
    print_r("Available Moves" . PHP_EOL);
    foreach ($choices as $key => $value){
        print_r("{$key} - {$value}" . PHP_EOL);
    }
}
function fillHelpTable(ConsoleTable $consoleTable, array $headers, array $mapBeats):void {
    array_unshift($headers, " ");
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
}

function generateHMAC(string $secretKey, string $choice):string {
    $hashHmacAlgorithm = hash_hmac_algos()[11];
    $hmacTurn = hash_hmac($hashHmacAlgorithm, $choice, $secretKey);

    return  $hmacTurn;
}

function main(): void {
    if (!hasParameters() || !hasUniqueParameters())
        exit(-1);

    $parameters = alignParameters();
    $secretKey = random_bytes(32);
    $isRun = true;
    $mapBeats = createMapBeats($parameters);
    $consoleTable = new ConsoleTable();
    fillHelpTable($consoleTable, $parameters, $mapBeats);
    $computerChoice = strval(random_int(1, count($parameters)));
    $hmacTurn = generateHMAC($secretKey, $parameters[$computerChoice]);

    while ($isRun) {
        print_r("Hmac : " . PHP_EOL . "{$hmacTurn}" . PHP_EOL);
        $choices = $parameters;
        addChoices($choices);
        printMenuChoice($choices);
        $playerChoice = readline("Player choice : ");

        if (!isset($choices[$playerChoice])){
            clearScreen();
            print_r("Invalid input" . PHP_EOL);
            continue;
        }

        $hmacTurn = generateHMAC($secretKey, $choices[$playerChoice]);

        if ($choices[$playerChoice] === $choices["?"]){
            $isHelpRun = true;
            while ($isHelpRun) {
                $consoleTable->display();
                $choicesHelp = array("0" => "exit");
                printMenuChoice($choicesHelp);
                $playerChoiceHelp = readline("Player choice : ");

                if (!isset($choicesHelp[$playerChoiceHelp])){
                    clearScreen();
                    print_r("Invalid input" . PHP_EOL);
                    continue;
                } elseif ($choices[$playerChoiceHelp] === $choices["0"]){
                    $isHelpRun = false;
                }
            }
        } elseif ($choices[$playerChoice] === $choices["0"]){
            $isRun = false;
        } else {
            print_r("Your move : {$choices[$playerChoice]}" . PHP_EOL);
            print_r("Computer move : {$choices[$computerChoice]}" . PHP_EOL);
            print_r("You {$mapBeats[$choices[$playerChoice]][$choices[$computerChoice]]}" . PHP_EOL);
            $isRun = false;
        }
    }

    print_r("Hmac key " . PHP_EOL . ": {$hmacTurn}" . PHP_EOL);
}

main();