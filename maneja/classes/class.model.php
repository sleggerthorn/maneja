<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.siteCreationHandler.php");
    abstract class model{
        //Attribute
        private $js = array();
        private $css_scripts;
        private $site;

        public function __construct(){
            setJs($js_path);
            setCssScripts($script_css);
            setSite($site_information);
        }
        //setter Methoden
        protected function setJs($js){
            array_push($this->js, $js);
        }

        protected function setCssScripts($script_css){
            $this->css_scripts = $script_css;
        }

        protected function setSite($site_information){
            $this->site = $site_information;
        }
        //getter Methoden
        protected function getJs(){
            return $this->js;
        }

        protected function getCssScripts(){
            return $this->css_scripts;
        }

        protected function getSite(){
            return $this->site;
        }

        protected function makeSite(){
        }
        abstract public function showModel();
    }
?>