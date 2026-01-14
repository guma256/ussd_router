<?php

class Controller {

    function __construct() {
    }

    public function LoadModel($name) {
        $path = 'models/' . $name . '_model.php';

        if (file_exists($path)) {
            require 'models/' . $name . '_model.php';
            $modelName = $name . '_Model';
            $this->model = new $modelName();
        }
    }
}

?>
