<?php

namespace Wyra\Kernel\View;

use Smarty;
use Wyra\Kernel\Kernel;

/**
 * Smarty implementation of WyRa
 *
 * Copyright (c) 2017, Raffael Wyss <raffael.wyss@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Raffael Wyss nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @autor       Raffael Wyss <raffael.wyss@gmail.com>
 * @copyright   2017 Raffael Wyss. All rights reserved.
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ViewSMARTY extends View
{

    /** @var null|Smarty $smarty */
    private $smarty = null;

    private $templatePath = '';


    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->setUpSmarty();
    }


    /**
     * Gibt die Daten vom HTML aus
     *
     * @param $data
     */
    public function show($data, $args = [], $echo = true)
    {
        $return = $data;
        $themeFolder = Kernel::$config->get('theme.folder');
        $themePath = Kernel::$config->get('rootPath').'/Themes/'.$themeFolder.'/src/Themes/'.$themeFolder.'/Template';
        if (isset($args['errortemplate'])) {
            $this->setVariables($args);
            $this->addContent(
                $themePath.'/'.$args['errortemplate'],
                $data,
                $args
            );
            $this->smarty->assign('footer', '');
            $this->smarty->display($themePath.'/markup.tpl');
        } elseif ($echo) {
            $this->setVariables($args);
            $this->addMetanavigation();
            $this->addSideBar();
            $this->addContent($args['template'], $data, $args);
            $this->addFooter();
            $this->smarty->display($themePath.'/markup.tpl');
        }
        return $return;
    }

    /**
     * Inhalt an Smarty hängen
     *
     * @param string $template
     * @param array $data
     * @param array $args
     */
    private function addContent($template, $data, $args)
    {
        $this->smarty->assign('data', $data);
        $this->smarty->assign('content', $this->smarty->fetch($this->getTemplate($template, $args)));
    }

    /**
     * Sidebar an Smarty hängen
     */
    private function addSideBar()
    {
        if (!Kernel::$config->get('installed')) {
            $this->smarty->assign('sidebar', '');
        } else {
            $this->smarty->assign('sidebar', $this->smarty->fetch($this->templatePath.'/sidebar.tpl'));
        }
    }

    /**
     * Metanavigation an Smarty hängen
     */
    private function addMetanavigation()
    {
        if (!Kernel::$config->get('installed')) {
            $this->smarty->assign('metanavigation', '');
        } else {
            $this->smarty->assign('metanavigation', $this->smarty->fetch($this->templatePath.'/metanavigation.tpl'));
        }
    }

    /**
     * Fusszeile an Smarty hängen
     */
    private function addFooter()
    {
        $usedTime = microtime(true) - Kernel::$startTime;
        $this->smarty->assign('usedTime', number_format($usedTime, 4));
        $this->smarty->assign('footer', $this->smarty->fetch($this->templatePath.'/footer.tpl'));

    }

    /**
     * Templatenamen ermitteln
     *
     * @param string $template
     * @param array $args
     *
     * @return string
     */
    private function getTemplate($template, $args)
    {
        if (isset($args['errortemplate'])) {
            return $template;
        } else {
            return Kernel::$config->get('rootPath').'/Plugins/'.$args['Plugin'].'/src/Plugins/'.$args['Plugin'].'/Template/'.$template;
        }

    }

    /**
     * Grundeinstellungen für das Smarty laden
     */
    private function setUpSmarty()
    {
        // Setzen der Template-Einstellungen
        $rootPath = Kernel::$config->get('rootPath');
        $themeFolder = Kernel::$config->get('theme.folder');
        $path = $rootPath."/Kernel/View/Smarty";
        $this->smarty->setTemplateDir($path . '/templates/');
        $this->smarty->setCompileDir($path . '/templates_c/');
        $this->smarty->setConfigDir($path . '/configs/');
        $this->smarty->setCacheDir($path . '/cache/');
        $this->templatePath = $rootPath.'/Themes/'.$themeFolder.'/src/Themes/'.$themeFolder.'/Template';

        // Allgemeines setzen
        $this->smarty->caching = Smarty::CACHING_OFF;
        $this->smarty->debugging = false;

    }

    /**
     * Variablen für das Smarty setzen
     */
    private function setVariables($args = array())
    {
        $config = Kernel::$config->getAll();
        $language = Kernel::$language->getAll();
        $this->smarty->assign('config', $config);
        $this->smarty->assign('language', $language);
        $this->smarty->assign(
            'elements',
            isset($args['elements']) ? $args['elements'] : ''
        );
        if (isset($args['error'])) {
            $this->smarty->assign('error', true);
        }

        // Extend-the Smarty
        $smartyExtender = new ViewSMARTYExtender();
        $this->smarty = $smartyExtender->registerPlugins($this->smarty, $args);
    }




}