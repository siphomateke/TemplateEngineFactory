<?php
require_once('TemplateEngine.php');

/**
 * TemplateEngineProcesswire
 *
 * @author Stefan Wanzenried <stefan.wanzenried@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License, version 2
 * @version 1.0.1
 */
class TemplateEngineProcesswire extends TemplateEngine implements Module, ConfigurableModule
{

    /**
     * @var TemplateFile
     */
    protected $template;


    /**
     * @inheritdoc
     */
    public function initEngine()
    {
        $this->template = new TemplateFile($this->getTemplatesPath() . $this->getFilename());
        $this->addHookAfter('TemplateFile::render', $this, 'hookRender');
    }


    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->template->set($key, $value);
    }


    /**
	 * Populate markup regions
	 * 
	 * @param $html
	 * 
	 */
    protected function populateMarkupRegions($html) {
		$markupRegions = new WireMarkupRegions();
		$this->wire($markupRegions);
		
		$pos = stripos($html, '<!DOCTYPE html');
		
		if($pos === false) {
			// if no doctype match, attempt an html tag match
			$pos = stripos($html, '<html'); 
		}
		
		// if no document start, or document starts at pos==0, then nothing to populate
		if(!$pos) {
			// there still may be region related stuff that needs to be removed like <region> tags
			$markupRegions->removeRegionTags($html);
			return;
		}
		
		// split document at doctype/html boundary
		$htmlBefore = substr($html, 0, $pos);
		$html = substr($html, $pos);
		$options = array('useClassActions' => true); 
		$config = $this->wire('config');
		$version = (int) $config->useMarkupRegions;
		
		if($config->installed >= 1498132609 || $version >= 2) {
			// If PW installed after June 21, 2017 do not use legacy class actions
			// as they are no longer part of the current WireMarkupRegions spec.
			// Can also force this behavior by setting $config->useMarkupRegions = 2;
			$options['useClassActions'] = false;
		}
		
        $markupRegions->populate($html, $htmlBefore, $options);

        return $html;
	}
    

    /**
     * Method executed after TemplateFile::render()
     *
     * @param HookEvent $event
     */
    public function hookRender(HookEvent $event)
    {
        $data = $event->return;

        if($this->wire('config')->useMarkupRegions) {
			$contentType = $template->contentType; 
			if(empty($contentType) || stripos($contentType, 'html') !== false) {
				$this->populateMarkupRegions($data);
			}
		}
    }


    /**
     * @inheritdoc
     */
    public function render()
    {
        return $this->template->render();
    }


    /**
     * @return array
     */
    public static function getDefaultConfig()
    {
        $config = parent::getDefaultConfig();
        return array_merge($config, array(
            'template_files_suffix' => 'php',
        ));
    }


    /**
     * Per interface Module, ConfigurableModule
     *
     */


    /**
     * @return array
     */
    public static function getModuleInfo()
    {
        return array(
            'title' => 'Template Engine ProcessWire',
            'version' => 101,
            'author' => 'Stefan Wanzenried',
            'summary' => 'ProcessWire templates for the TemplateEngineFactory',
            'href' => '',
            'singular' => false,
            'autoload' => false,
            'requires' => array('TemplateEngineFactory'),
        );
    }


    /**
     * @param array $data Array of config values indexed by field name
     * @return InputfieldWrapper
     */
    public static function getModuleConfigInputfields(array $data)
    {
        $data = array_merge(self::getDefaultConfig(), $data);
        $wrapper = parent::getModuleConfigInputfields($data);
        return $wrapper;
    }

}
