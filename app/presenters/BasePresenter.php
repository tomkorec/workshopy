<?php

namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public function beforeRender(){
        $refreshCode = substr(md5(microtime()),rand(0,26),5);
        $this->template->refreshCode = $refreshCode;
    }
}
