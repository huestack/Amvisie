<?php
namespace Amvisie\Core\Web;

/**
 * Provides helper methods to use in View.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
final class View
{
    private 
            /**
             * A physical path of view file.
             * @var string Path of view file.
             */
            $viewPath = null,

            /**
             * Path of layout file if available.
             * @var string
             */
            $layout,
            
            /**
             * Can use layout with view?
             * @var bool
             */
            $useLayout,
            
            /**
             * An array thats holds content of sections.
             * @var array
             */
            $sections = array(),
            
            /**
             * Name of a section that is recently started to render.
             * @var string 
             */
            $sectionName = null,
            
            /**
             * A string that is rendered in <title> tag of layout.
             * @var string
             */
            $title,
            
            /**
             * Content of a view rendered.
             * @var string 
             */
            $bodyContent,
            
            /**
             * An helper instance that renders Html controls and other tags.
             * @var HtmlHelper
             */
            $html,
            
            /**
             * An helper instance that creates urls based on given configuration.
             * @var \Amvisie\Core\Web\UrlHelper
             */
            $url,
            
            /**
             * A context object that handles view data.
             * @var \Amvisie\Core\Web\ViewContext
             */
            $viewContext,
            
            /**
            * An object that has dynamic properties.
            * @var stdClass 
            */
            $viewObject,
            /**
             * A collection of data that flows from Controller to View.
             * @var array An array of data.
             */
            $viewPocket,
            
            /**
             * @var \Amvisie\Core\TempDataArray
             */
            $tempPocket,
            
            /**
             * A model object for current view.
             * @var \Amvisie\Core\BaseModel
             */
            $model = null,
            
            /**
             * A reference to object of \Amvisie\Core\HttpRequest class.
             * @var \Amvisie\Core\HttpRequest
             */
            $request,
            
            /**
             * A reference to object of \Amvisie\Core\HttpResponse class.
             * @var \Amvisie\Core\HttpResponse
             */
            $response;
    
    public function __construct(string $name, ViewContext &$viewContext, &$model, bool $useLayout = true)
    {
        $this->viewContext = $viewContext;
        $this->viewObject = $this->viewContext->viewObject;
        $this->viewPocket =  $this->viewContext->viewPocket;
        $this->tempPocket = $this->viewContext->tempPocket;
        
        $this->viewPath = $this->resolveViewFile($name);
        
        if ($this->viewPath == null) {
            throw new \Exception('Error in loading view file.');
        }
        
        $this->useLayout = $useLayout;
        
        if ($this->useLayout) {
            $viewStartFile = $this->viewContext->getRoute()->getDirectory() . VIEW_DIR . '_ViewStart' . EXTN;
            if (file_exists($viewStartFile)) {
                include $viewStartFile;
            }
        }
        
        $this->model = $model;
        $this->request = $this->viewContext->request();
        $this->response = $this->viewContext->response();
        
        $this->html = new HtmlHelper($this->viewContext, $this->model);
        $this->url = new UrlHelper($this->viewContext->getRoute());
    }
    
    /**
     * Sets a model object for view. It could be null.
     * @param mixed $model
     * @internal Do not call this method. It may be removed in future releases.
     */
    public function setModel(&$model) : void
    {
        $this->model = $model;
    }
    
    /**
     * Gets model object associated with this view.
     * @return mixed Any model object.
     */
    public function &getModel()
    {
        return $this->model;
    }

    /**
     * Gets a bool indication to use master layout to render view with.
     * @return bool true if the view has to be associated with master layout; otherwise false.
     */
    public function useLayout() : bool
    {
        return $this->useLayout;
    }
    
    /**
     * Sets a master layout. A default layout is supposed to be _Layout.php file that is available either in 
     * view folder of area or in view folder root.
     * @param string $layout A physical path of layout file.
     */
    public function setLayout(string $layout) : void
    {
        $this->layout = $layout;
    }
    
    /**
     * Gets a path to layout file.
     * @return string A path to layout file.
     */
    public function getLayout() : string
    {
        return $this->layout;
    }
    
    /**
     * Sets the title of page.
     * @param string $title
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }
    
    /**
     * Gets a title of page.
     * @return string
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * Used in Layout file to include sections.
     * @param string $sectionName
     */
    public function startSection(string $sectionName) : void
    {
        if ($this->useLayout) {
            $this->sectionName = $sectionName;
        }
        
        ob_start();
    }
    
    /**
     * Ends rendering section.
     */
    public function endSection() : void
    {
        if ($this->useLayout) {
            $this->sections[$this->sectionName] = ob_get_clean();
            $this->sectionName = null;
        } else {
            ob_end_clean();
        }
    }
    
    /**
     * Writes a content of section in output stream.
     * @param string $sectionName
     */
    public function section(string $sectionName) : void
    {
        if (!$this->useLayout || !array_key_exists($sectionName, $this->sections)) {
            return;
        }

        echo $this->sections[$sectionName];
    }

    /**
     * Writes a view's content in output stream.
     */
    public function body() : void
    {
        echo $this->bodyContent;
    }
    
    /**
     * Renders a view.
     */
    public function render() : void
    {
        if ($this->useLayout && $this->layout){
            ob_start();
            include $this->viewPath;
            $this->bodyContent = ob_get_clean();
        }
        else{
            include $this->viewPath;
        }
    }
    
    /**
     * Renders a layout.
     */
    public function renderLayout() : void
    {
        if (!$this->useLayout) {
            return;
        }

        if ($this->layout) {
            include $this->layout;
        }
    }
    
    /**
     * Sets a model defined by view to use.
     * @param string $modelName A name of model with current namespace.
     * @throws \Exception Exception is raised when model file is not found.
     */
    public function useModel(string $modelName)
    {
        if ($this->model == null) {
            $this->html->setModel(new $modelName());
        }
    }
    
    private function resolveViewFile(string $name)
    {
        // $name may contain path to view file relative to root app directory.
        // If it is a path, simply load the view file, otherwise, determine view file by name.
        $file = APP_PATH . $name;
        
        if (!is_file($file)) {
            foreach ($this->viewContext->getViewDirectories() as $dir) {
                $file = $dir . $name . EXTN;
                if (file_exists($file)){
                    break;
                }
            }
        }

        return $file;
    }
}
