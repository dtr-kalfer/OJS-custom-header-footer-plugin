<?php
namespace APP\plugins\generic\customHeadFoot;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use APP\core\Application;

class CustomHeadFootPlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = NULL) {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                Hook::add('TemplateManager::display', array($this, 'handleDisplay'));
            }
            return true;
        }
        return false;
    }

    public function getDisplayName() {
        return 'Custom Header/Footer Background with opacity';
    }

    public function getDescription() {
        return 'Allows admin to set a custom image for header and footer background with opacity.';
    }

    /**
     * Injects the "Settings" link under the plugin name in the OJS plugin grid row
     */
    public function getActions($request, $verb) {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : [],
            parent::getActions($request, $verb)
        );
    }

    /**
     * Handles displaying the settings modal and saving user data
     */
    public function manage($args, $request) {
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : \PKP\core\PKPApplication::SITE_CONTEXT_ID;

        switch ($request->getUserVar('verb')) {
            case 'settings':
                $templateMgr = TemplateManager::getManager($request);

                // Process the form submission
                if ($request->getUserVar('save')) {
                    $this->updateSetting($contextId, 'headerBackgroundImage', $request->getUserVar('headerBackgroundImage'));
                    $this->updateSetting($contextId, 'headerOpacity', $request->getUserVar('headerOpacity'));
                    $this->updateSetting($contextId, 'footerBackgroundImage', $request->getUserVar('footerBackgroundImage'));
                    $this->updateSetting($contextId, 'footerOpacity', $request->getUserVar('footerOpacity'));

                    // Return a successful JSON notice to close the modal
                    return new JSONMessage(true);
                }

                // Load saved values into the template variables
                $templateMgr->assign([
                    'headerBackgroundImage' => $this->getSetting($contextId, 'headerBackgroundImage'),
                    'headerOpacity' => $this->getSetting($contextId, 'headerOpacity'),
                    'footerBackgroundImage' => $this->getSetting($contextId, 'footerBackgroundImage'),
                    'footerOpacity' => $this->getSetting($contextId, 'footerOpacity'),
                    'pluginName' => $this->getName(),
                ]);

                return new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('settings.tpl')));
        }
        return parent::manage($args, $request);
    }

    public function handleDisplay($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        // Dynamically target ALL public frontend pages, but skip the backend dashboard
        if (strpos($template, 'frontend/') !== 0) {
            return false;
        }

        $request = \APP\core\Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : \PKP\core\PKPApplication::SITE_CONTEXT_ID;

        $headerImageUrl = $this->getSetting($contextId, 'headerBackgroundImage');
        $headerOpacity = $this->getSetting($contextId, 'headerOpacity');
        $footerImageUrl = $this->getSetting($contextId, 'footerBackgroundImage');
        $footerOpacity = $this->getSetting($contextId, 'footerOpacity');

        $resolveUrl = function($url) use ($request) {
            if (empty($url)) return '';
            if (preg_match('/^https?:\/\//', $url)) return $url;
            return rtrim($request->getBaseUrl(), '/') . '/' . ltrim($url, '/');
        };

        $headerUrl = $resolveUrl($headerImageUrl);
        $footerUrl = $resolveUrl($footerImageUrl);

        $styles = '';
        if ($headerUrl) {
            $styles .= "
                .pkp_structure_head {
                    position: relative;
                    z-index: 1;
                }
                .pkp_structure_head::before {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background-image: url('" . $headerUrl . "');
                    background-size: cover;
                    background-position: center;
                    opacity: " . (float)$headerOpacity . ";
                    z-index: -1;
                }
            ";
        }
        if ($footerUrl) {
            $styles .= "
                .pkp_structure_footer_wrapper {
                    position: relative;
                    z-index: 1;
                }
                .pkp_structure_footer_wrapper::before {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background-image: url('" . $footerUrl . "');
                    background-size: cover;
                    background-position: center;
                    opacity: " . (float)$footerOpacity . ";
                    z-index: -1;
                }
                .pkp_structure_footer,
                .pkp_footer_content,
                .pkp_brand_footer {
                    background: transparent !important;
                }
            ";
        }

        if (!empty($styles)) {
            $styleBlock = '<style type="text/css">' . $styles . '</style>';
            $templateMgr->addHeader('customHeadFootStylesInline', $styleBlock);
        }

        return false;
    }


}