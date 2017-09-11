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
    
    public function parse() : void
    {
        if (array_search($this->getHttpMethod(), $this->usePhpInputFor) === false) {
            $postArray = filter_input_array(INPUT_POST);
            
            $this->data = $postArray ? $postArray : [];
            $this->files = $_FILES;
        } else {
            $this->parseFormData();
        }
    }
    
    public function convertAs(\ReflectionClass $object)
    {
        $instance = $object->newInstance();
        foreach ($this->data as $key => $value) {
            if ($object->hasProperty($key)) {
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
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block)) {
                continue;
            }

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== false) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $this->files['files'][$matches[1]] = $matches[2];
                
            } else { // parse all other fields
                
                if (strpos($block, 'filename') !== false) {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                    preg_match('/Content-Type: (.*)?/', $matches[3], $mime);

                    // match the mime type supplied from the browser
                    $image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $matches[3]);

                    // get current system path and create tempory file name & path
                    $path = sys_get_temp_dir() . '/php' . substr(sha1(rand()), 0, 6) . '.tmp';

                    // write temporary file to emulate $_FILES super global
                    $size = file_put_contents($path, ltrim($image));
                    
                    $name = '';
                    
                    // Did the user use the infamous &lt;input name="array[]" for multiple file uploads?
                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $name = $tmp[1];
                    } else {
                        $name = $matches[1];
                    }
                    
                    $this->files[$name]['name'] = $matches[2];
                    $this->files[$name]['type'] = $mime[1];
                    
                    if ($size === false) {
                        $this->files[$name]['tmp_name'] = $path;
                        $this->files[$name]['error'] = '';
                        $this->files[$name]['size'] = $size;
                    } else {
                        $this->files[$name]['error'] = 'Cannot write into temp file.';
                    }
                } else {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);

                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $this->data[$tmp[1]][] = htmlspecialchars($matches[2]);
                    }  else {
                        $this->data[$matches[1]] = htmlspecialchars($matches[2]);
                    }
                }
            }
        }
    }
}
