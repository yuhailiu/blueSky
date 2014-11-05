<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\Json\Json;

class DownloadController extends AbstractActionController
{

    public function andriodAction()
    {
        $downPath = $this->getDownloadLocation();
        $filename = $downPath . "/Olivine.apk";
        // get the file
        $file = file_get_contents($filename);
        $filesize=abs(filesize($filename));
        
        if ($file) {
            return $this->returnFileResponse($file,$filesize);
        }
    }

    public function IndexAction()
    {
        $result = array(
            "flag" => "download"
        );
        return $this->returnJson($result);
    }

    /**
     * put the file to reponse
     *
     * @param File $file            
     * @return response
     * @author yuhai liu
     */
    protected function returnFileResponse($file, $filesize)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename=olivine.apk',
            'Content-Length' => $filesize,
            'cache-control' => 'public',
            'Pragma' => 'public'
        ));
        $response->setContent($file);
        return $response;
    }

    /**
     *
     * @return boolean
     */
    protected function getDownloadLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['download'])) {
            return $config['module_config']['download'];
        } else {
            return FALSE;
        }
    }

    /**
     * change an array to Json and return response
     *
     * @param array $array            
     * @return \Zend\Stdlib\ResponseInterface
     */
    protected function returnJson($result)
    {
        $json = Json::encode($result);
        
        $response = $this->getEvent()->getResponse();
        $response->setContent($json);
        
        return $response;
    }
}
