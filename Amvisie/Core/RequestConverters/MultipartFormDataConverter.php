<?php

namespace Amvisie\Core\RequestConverters;

/**
 * This converter parses multipart-formdata data available in body into array and object.
 *
 * @author Ritesh
 */
class MultipartFormDataConverter extends BaseConverter
{
    private $usePhpInputFor = array('put', 'patch', 'delete');
    
    public function parse() : bool
    {
        if (array_search($this->getHttpMethod(), $this->usePhpInputFor) === false) {
            // Cannot include 
            $postArray = filter_input_array(INPUT_POST);
            
            $this->data = $postArray ?? [];
            $this->files = $this->getFromFilesGlobal();
            
        } else {
            $this->parseFormData();
        }
        
        return true;
    }
    
    public function convertAs(\ReflectionClass $object)
    {
        $instance = $object->newInstance();
        foreach ($this->data as $key => $value) {
            if ($object->hasProperty($key)) {
                if ($instance instanceof \Amvisie\Core\BaseModel) {
                    $propertyInfo = $instance->getMeta()->getPropertyTypeInfo($key);
                    
                    if($propertyInfo && $propertyInfo->getType() !== \Amvisie\Core\Annotations\PropertyTypes::ARR && is_array($value)) {
                        continue;
                    }
                }
                
                $instance->{$key} = htmlspecialchars($value);
            }
        }
        return $instance;
    }
    
    private function parseFormData() {
        $matches = array();
        
        // read incoming data
        $input = file_get_contents('php://input');
        
        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', filter_input(INPUT_SERVER, 'CONTENT_TYPE'), $matches);
        
        // content type is probably regular form-encoded
        if (!count($matches)) {
            // we expect regular puts to containt a query string containing data
            parse_str(urldecode($input), $this->data);
            return;
        }
        
        $boundary = $matches[1];
        
        // split content by boundary and get rid of last -- element
        $blocks = preg_split("/-+$boundary/", $input);
        array_pop($blocks);

        // loop data blocks
        foreach ($blocks as $block) {
            if (empty($block)) {
                continue;
            }
            
            // parse uploaded files
            if (strpos($block, 'filename') !== false) {
                $this->setFile($block, $matches);
            } else {
                $this->setData($block, $matches);
            }
        }
    }
    
    private function setData(string $block, array $matches)
    {
        // match "name" and optional value in between newline sequences
        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);

        if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
            $this->data[$tmp[1]][] = htmlspecialchars($matches[2]);
        }  else {
            $this->data[$matches[1]] = htmlspecialchars($matches[2]);
        }
    }
    
    private function setFile(string $block, array &$matches) : void
    {
        // match "name" and optional value in between newline sequences
        preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
        preg_match('/Content-Type: (.*)?/', $matches[3], $mime);

        // match the mime type supplied from the browser
        $fileName = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $matches[3]);

        // get current system path and create tempory file name & path
        $path = sys_get_temp_dir() . '/php' . substr(sha1(rand()), 0, 6) . '.tmp';

        // write temporary file to emulate $_FILES super global
        $size = file_put_contents($path, ltrim($fileName));

        $name = '';
        $isArray = false;
        
        
        // Did the user use the infamous &lt;input name="array[]" for multiple file uploads?
        if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
            $name = $tmp[1];
            $isArray = true;
        } else {
            $name = $matches[1];
        }

        $file = new \Amvisie\Core\HttpFile($matches[2], $mime[1]);

        if ($size !== false) {
            $file->setTempPath($path);
            $file->setSize($size);
        } else {
            $file->setError('Cannot write into temp file.');
        }

        if($isArray) {
            $this->files[$name][] = $file;
        } else {
            $this->files[$name] = $file;
        }
    }
    
    private function getFromFilesGlobal()
    {
        $array = array();
        foreach ($_FILES as $key => $value) {
            if( is_array($value['name'])) {
                $array[$key] = $this->getFilesAsHttpFiles($value);
            } else {
                $file = new \Amvisie\Core\HttpFile($value['name'], $value['type']);
                $file->setTempPath($value['tmp_name']);
                $file->setSize($value['size'] ?? 0);
                $file->setError($value['error'] !== 0 ? $value['error'] : null);
                
                $array[$key] = $file;
            }
        }
        
        return $array;
    }
    
    private function getFilesAsHttpFiles(array &$from) : array
    {
        $array = array();   // Array of instances of HttpFile class.
        
        for ($index = 0; $index < count($from['name']); $index++) {
            $file = new \Amvisie\Core\HttpFile($from['name'][$index], $from['type'][$index]);
            $file->setTempPath($from['tmp_name'][$index]);
            $file->setSize($from['size'][$index] ?? 0);
            $file->setError($from['error'][$index] !== 0 ? $from['error'][$index] : null);
            
            $array[] = $file;
        }
        
        return $array;
    }
}
