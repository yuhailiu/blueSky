<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Stdlib\ArrayUtils;
use Zend\Json\Json;

class MediaManagerController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected $photos;

    protected function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    protected function getFileUploadLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['image_upload_location'])) {
            return $config['module_config']['image_upload_location'];
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

    protected function processUploadAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $request = $this->getRequest();
        $post = $request->getPost();
        
        // store the result for json return
        $result = array(
        	'flag' => true,
            'message' => "the file have been upload and update"
        );
        
        if ($request->isPost()) {
            
            $uploadFile = $this->params()->fromFiles('imageupload');
            $form->setData($request->getPost());
            
            if (true) {
                // Fetch Configuration from Module Config
                $uploadPath = $this->getFileUploadLocation();
                
                // Prepare to save Uploaded file
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                $adapter->setDestination($uploadPath);
                
                // Limit the extensions to jpg,bmp,gif,jpeg and png files
                $adapter->addValidator('Extension', false, 'jpg, png, bmp, gif, jpeg');
                
                // Limit the size of all files to be uploaded to maximum 2MB and mimimum 20 bytes
                $adapter->addValidator('FilesSize', false, array(
                    'min' => 1,
                    'max' => '1MB'
                ));
                
                // Verify is this a empty submit
                // file uploaded & is valid
                if ($adapter->isUploaded() && $adapter->isValid()) {
                    // upload type: 1 - org, 2 - user
                    // if request from orgnization then update orgnization table
                    // if request from user then update userInfo table
                    $uploadType = (int) $post->uploadType;
                    if (1 == $uploadType) {
                        // upload the file to org;
                        try {
                            $this->uploadOrgnizationLogo($email, $uploadFile, $adapter);
                        } catch (\Exception $e) {
                            $result = array(
                            		'flag' => false,
                            		'message' => 'error when upload to orgnization'
                            );
                        }
                    } elseif (2 == $uploadType) {
                        // upload the file to usertable
                        try {
                            $this->uploadUserInfo($email, $uploadFile, $adapter);
                        } catch (\Exception $e) {
                            $result = array(
                            		'flag' => false,
                            		'message' => 'error when upload to userinfo'
                            );
                        }
                    } else {
                        $result = array(
                            'flag' => false,
                            'message' => 'uploadType is wrong'
                        );
                    }
                } else {
                    $result = array(
                        'flag' => false,
                        'message' => 'validation is wrong'
                    );
                }
            } else {
                $result = array(
                    'flag' => false,
                    'message' => 'form validation is wrong'
                );
            }
        } else {
            $result = array(
                'flag' => false,
                'message' => 'it must be post'
            );
        }
        return $this->returnJson($result);
    }

    /**
     * update userInfo table and create a user photo file name by mktime
     *
     * @param string $email            
     * @param File $uploadFile            
     * @param Zend\File\Transfer\Adapter\Http $adapter            
     * @return boolean \Zend\Stdlib\ResponseInterface
     */
    protected function uploadUserInfo($email, $uploadFile, $adapter)
    {
        // get userinfo by email
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try {
            $userInfo = $userInfoTable->getUserInfoByEmail($email);
        } catch (\Exception $e) {
            MyUtils::writelog("can't get userinfor from upload controller" . $e);
            throw new \Exception($e);
        }
        //creater the filename
        $sysName = img.$userInfo->id;
        
        // if the user has filename use it, otherwise create a new one
//         if ('defaultphoto.jpg' == $userInfo->filename || null == $userInfo->filename) {
//             // Set the system name with Unix time add a 3 bit digitals
//             //$sysName = mktime() . rand(100, 999);
            
//         } else {
//             // keep the original
//             $sysName = $userInfo->filename;
//         }

        
        $adapter->addFilter('Rename', $sysName, $uploadFile['name']);
        
        // Save the update photo
        if ($adapter->receive($uploadFile['name'])) {
            
            // genarate the thumbfile and give the name to userInfo
            $userInfo->thumbnail = $this->generateThumbnail($sysName);
            
            // update the DB if it's a new file
            if ($sysName != $userInfo->filename) {
                $userInfo->filename = $sysName;
                try {
                    $userInfoTable->updateUserInfo($userInfo);
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write userinfo to DB from upload controller" . $e);
                    throw new \Exception($e);
                }
            }
        }else{
            throw new \Exception("can't write the file to filesystem");
        }
    }

    /**
     * create the logo name by org id and update the org table by id
     *
     * @param string $email            
     * @param File $uploadFile            
     * @param Zend\File\Transfer\Adapter\Http $adapter            
     * @return throw \Exception
     */
    protected function uploadOrgnizationLogo($email, $uploadFile, $adapter)
    {
        $sysName = "logo";
        // get userinfo by email
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgTable->getOrgnizationByCreaterEmail($email);
        } catch (\Exception $e) {
            MyUtils::writelog("can't get userinfor from upload controller" . $e);
            throw new \Exception("can't get userinfo".$e);
        }
        
        $sysName = $sysName . $org->id;
        
        $adapter->addFilter('Rename', $sysName, $uploadFile['name']);
        
        // Save the update photo
        if ($adapter->receive($uploadFile['name'])) {
            
            // genarate the thumbfile and give the name to userInfo
            $org->org_logo_thumbnail = $this->generateThumbnail($sysName);
            
            // update the DB if it's a new file
            if ($sysName != $org->org_logo) {
                $org->org_logo = $sysName;
                try {
                    $orgTable->saveOrgnization($org);
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write orgnization to DB from upload controller" . $e);
                    throw new \Exception($e);
                }
            }
        } else {
            MyUtils::writelog("can't write the file to filesystem");
            throw new \Exception($e);
        }
    }

    /**
     * generate thumbfile from image
     *
     * @param string $imageFileName            
     * @return string thumbfile name
     */
    protected function generateThumbnail($imageFileName)
    {
        $path = $this->getFileUploadLocation();
        $sourceImageFileName = $path . '/' . $imageFileName;
        $thumbnailFileName = 'tn_' . $imageFileName;
        
        $imageThumb = $this->getServiceLocator()->get('WebinoImageThumb');
        $thumb = $imageThumb->create($sourceImageFileName, $options = array());
        $thumb->resize(50, 50);
        $thumb->save($path . '/' . $thumbnailFileName);
        
        return $thumbnailFileName;
    }

    protected function uploadAction()
    {
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $viewModel = new ViewModel(array(
            'form' => $form
        ));
        return $viewModel;
    }

    /**
     * get id from Http
     *
     * @return Response with pic
     */
    protected function showImageAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $key = $this->params()->fromRoute('key');
        
        // Fetch Configuration from Module Config
        $uploadPath = $this->getFileUploadLocation();
        
        // if subaction is logo,bind the logo path
        if ($this->params()->fromRoute('subaction') == 'logo') {
            $id = (int)$key;
            // get the logo file name from org table
            $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
            $org = $orgTable->getOrgnizationById($id);
            // add the logo's filename to the $filename
            $filename = $uploadPath . "/" . $org->org_logo;
        }elseif ($this->params()->fromRoute('subaction') == 'logoThumb'){
            $id = (int)$key;
            // get the logo file name from org table
            $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
            $org = $orgTable->getOrgnizationById($id);
            // add the logo's filename to the $filename
            $filename = $uploadPath . "/" . $org->org_logo_thumbnail;
        }else {
            $validation = new EmailAddress();
            if (!$validation->isValid($key)) {
                $result = array(
                	'flag' => false,
                    'message' => "invalidate email address"
                );
            	return $this->returnJson($result);
            }
            // check the filename from table and bind it
            $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
            try {
                $userInfo = $userInfoTable->getUserInfoByEmail($key);
            } catch (\Exception $e) {
                return $this->returnResponse("can't find the userInfo of $key");
            }
            
            // bind the thumbnail path
            if ($this->params()->fromRoute('subaction') == 'thumb') {
                $filename = $uploadPath . "/" . $userInfo->thumbnail;
            } else {
                $filename = $uploadPath . "/" . $userInfo->filename;
            }
        }
        //get the file
        $file = file_get_contents($filename);
        if ($file) {
            return $this->returnFileResponse($file);
        } else {
            return $this->returnResponse("can't open the file");
        }
    }

    /**
     * put the file to reponse
     *
     * @param File $file            
     * @return response
     * @author yuhai liu
     */
    protected function returnFileResponse($file)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename="' . $user->filename . '"'
        ));
        $response->setContent($file);
        
        return $response;
    }

    /**
     * put the mix var to reponse
     *
     * @param mix $data            
     * @return response
     */
    protected function returnResponse($data)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($data);
        return $response;
    }
}    

