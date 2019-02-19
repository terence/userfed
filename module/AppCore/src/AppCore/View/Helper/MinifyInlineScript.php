<?php

namespace AppCore\View\Helper;

class MinifyInlineScript extends MinifyHeadScript
{
	/**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Txl_View_Helper_MinifyInlineScript';
	public function __invoke($mode = self::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
	{
		return parent::__invoke($mode, $spec, $placement, $attrs, $type);
	}
}
