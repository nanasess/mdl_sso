<?php

class Sso extends SC_Plugin_Base
{
    public function loadClassFileChange(&$classname, &$classpath) {
        if ($classname === "SC_Customer_Ex") {
            $classpath = __DIR__."/class/SC_Customer_Sso_Ex.php";
            $classname = "SC_Customer_Sso_Ex";
        }
    }
}
