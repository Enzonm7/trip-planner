<?php
//Implement Builder.php and Config.php to create a strategy for calling the python script and getting the results

declare(strict_types=1);

final class PythonStrategy {

    private string $cmd;

    private array $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    private $process;
    private $pipes;

    public function __construct() {
        // Chemin relatif depuis travel_page/
        $scriptPath = __DIR__ . '/../test_algo/main.py';
        $this->cmd  = 'py ' . escapeshellarg($scriptPath); 

        $this->process = proc_open(
            $this->cmd,
            $this->descriptors,
            $this->pipes
        );
    }

    public function run($data): mixed {
        if (!is_resource($this->process)) {
            return null;
        }

        fwrite($this->pipes[0], $data);
        fclose($this->pipes[0]);

        $output = stream_get_contents($this->pipes[1]);
        $errors = stream_get_contents($this->pipes[2]);
        fclose($this->pipes[1]);
        fclose($this->pipes[2]);
        proc_close($this->process);

        if (!empty($errors)) {
            error_log("PythonStrategy stderr: " . $errors);  // visible dans les logs PHP
        }

        return json_decode($output, true);
    }
}