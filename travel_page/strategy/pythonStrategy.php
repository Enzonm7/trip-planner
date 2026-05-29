<?php
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
        // Chemin relatif vers main.py depuis la racine du projet
        $mainPy = dirname(__DIR__) . '/test_algo/main.py';
        // "python" pour Windows (Laragon), "python3" pour Linux/Mac
        $interpreter = (PHP_OS_FAMILY === 'Windows') ? 'python' : 'python3';
        $this->cmd = $interpreter . ' ' . escapeshellarg($mainPy);

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
        fclose($this->pipes[1]);

        $stderr = stream_get_contents($this->pipes[2]);
        fclose($this->pipes[2]);

        proc_close($this->process);

        if ($stderr) {
            error_log("[PythonStrategy] stderr: " . $stderr);
        }

        return json_decode($output, true);
    }
}
?>
