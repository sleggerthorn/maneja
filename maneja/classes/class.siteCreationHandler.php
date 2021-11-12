<?php
    class siteCreationHandler{
        //Attribute
        private $template;
        private $js_scripts;
        private $css_scripts;
        private $content;
        //setter Methoden
        private function setTemplate($template_name){
            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/html/" . $template_name)){
                $this->template = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/template/html/" . $template_name);
            }else{
                echo "Template not found.";
            }
        }

        private function setJsScripts($js_name){
            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/js/jquery.min.js")){
                $this->js_scripts = '<script src="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].":82".'/template/js/jquery.min.js" defer></script>';
            }else{
                echo "JQuery not found";
                die;
            }
            
            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/js/menu.js")){
                $this->js_scripts .= '<script src="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].":82".'/template/js/menu.js" defer></script>';
            }else{
                echo "JQuery not found";
                die;
            }

            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/js/".$js_name)){
                $this->js_scripts .= '<script src="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].":82".'/template/js/'.$js_name.'" defer></script>';
            }else{
                echo "JavaScript not found";
                die;
            }
        }

        private function setCssScripts($css_name){
            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/css/bootstrap.4.3.1.min.css")){
                $this->css_scripts = '<link rel="stylesheet" type="text/css" href="/template/css/bootstrap.4.3.1.min.css" defer>';
            }else{
                echo "Bootstrap not found";
            }

            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/template/css/" . $css_name)){
                $this->css_scripts .= '<link rel="stylesheet" type="text/css" href="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].":82".'/template/css/'.$css_name.'">';
            }else{
                echo "CSS Script not found";
            }
        }

        private function setContent($site_content){
            $this->content = $site_content;
        }
        //getter Methode
        private function getTemplate(){
            return $this->template;
        }

        private function getJsScripts(){
            return $this->js_scripts;
        }

        private function getCssScripts(){
            return $this->css_scripts;
        }

        private function getContent(){
            return $this->content;
        }
        
        //Hauptmethode
        public function createSite($template, $content){
            $this->setTemplate($template);
            for($x = 0; $x != sizeof($content["replacement"][1]); $x++){
                $this->setJsScripts($content["replacement"][1][$x]);
            }
            $this->setCssScripts($content["replacement"][0]);
            $content["replacement"][0] = $this->getCssScripts();
            $content["replacement"][1] = $this->getJsScripts();
            return str_replace($content["keyword"], $content["replacement"], $this->getTemplate());
        }
        
    }
?>
