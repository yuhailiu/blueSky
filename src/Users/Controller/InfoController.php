<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class InfoController extends AbstractActionController
{

    protected function aboutAction()
    {
        $view = new ViewModel();
        return $view;
    }

    protected function joinAction()
    {
        $view = new ViewModel();
        return $view;
    }

    protected function helpAction()
    {
        $view = new ViewModel();
        return $view;
    }

    protected function investorsAction()
    {
        $view = new ViewModel();
        return $view;
    }

    protected function termsAction()
    {
        $view = new ViewModel();
        return $view;
    }
}
