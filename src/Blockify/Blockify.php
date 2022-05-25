<?php

namespace Blockify;

use Blockify\Interfaces\errorApplication;
use ReflectionClass;
use ReflectionException;

/**
 * @author king m a kh
 * @version 1.0
 */
final class Blockify
{
    private bool $isPage;
    private string $requestController;
    private string $requestFunction;
    private array $jsonArguments;

    public function __construct(public string $_url, public string $defaultController, public errorApplication $errorController)
    {
        $url = explode('/', filter_var($_url, FILTER_SANITIZE_URL));

        $this->isPage = $_SERVER['REQUEST_METHOD'] == 'GET';

        $this->requestController = (($url[0] ?? '') == "") ? $defaultController : $url[0];

        if (!$this->isPage) {
            $this->requestFunction = (($url[1] ?? '') == "") ? "onRequest" : $url[1];
            $this->jsonArguments = json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    /**
     * @throws ReflectionException
     */
    public function start(string $viewPath, string $appPath, string $namespace)
    {
        if ($this->isPage) {
            if (!(file_exists("$viewPath$this->requestController.html") || file_exists("$viewPath$this->requestController.php")))
                return $this->errorController::viewError($this->requestController);

            echo self::page_request($viewPath);

        } else {
            if (!file_exists("$appPath$this->requestController.php")) {
                return $this->errorController::classError($this->requestController)->toString();
            } else {
                require_once "$appPath$this->requestController.php";

                $instance = new ReflectionClass("$namespace$this->requestController");

                if (!$instance->hasMethod($this->requestFunction))
                    return $this->errorController::functionError($this->requestFunction)->toString();
                else {
                    $method = $instance->getMethod($this->requestFunction);

                    if (!$method->hasReturnType() || $method->getReturnType()->getName() != "Blockify\Models\Api") {
                        return "no standard type";

                    } else {
                        if (count($method->getParameters()) < count($this->jsonArguments))
                            return $this->errorController::parameterCountError(count($method->getParameters()))->toString();
                        else {
                            $notExistKeys = [];
                            $parameters = [];

                            $parameterTypes = [];

                            foreach ($method->getParameters() as $parameter) {
                                if (!array_key_exists($parameter->getName(), $this->jsonArguments))
                                    $notExistKeys[] = $parameter->getName();

                                $parameterTypes[] = $parameter->getType();
                                $parameters[] = $parameter->getName();
                            }

                            if (count($notExistKeys) > 0) {
                                $comment = $method->getDocComment();
                                preg_match_all('/@param[a-zA-Z\d $_]+/i', $comment, $prob);

                                $prob = $prob[0];
                                $docs = [];

                                foreach ($prob as $item)
                                    $docs[] = preg_replace('/@param +(string|int|bool|array) +\$[a-z1-9_]+ */i', '', $item);

                                $args = [];

                                foreach ($parameters as $index => $parameter) {
                                    $args[] = [
                                        "var" => $parameter,
                                        "doc" => $docs[$index] ?? ""
                                    ];
                                }
                                return $this->errorController::parameterError($args)->toString();
                            }
                        }
                    }
                }
            }
            echo self::api_request($appPath, $namespace);
        }
    }

    private function page_request(string $viewPath): string
    {
        header("Content-Type: text/html;");
        if (file_exists("$viewPath$this->requestController.html"))
            return fread(
                fopen("$viewPath$this->requestController.html", "r"),
                filesize("$viewPath$this->requestController.html"));
        else {
            ob_start();
            require_once "$viewPath$this->requestController.php";
            return ob_get_clean();
        }
    }

    private function api_request(string $appPath, string $namespace): string
    {
        ob_start();

        $package = "$namespace$this->requestController";
        $instance = new $package();

        $result = call_user_func_array([$instance, $this->requestFunction], $this->jsonArguments);

        ob_clean();

        header('Content-Type: application/json; charset=utf-8');

        return $result->toString();
    }
}
