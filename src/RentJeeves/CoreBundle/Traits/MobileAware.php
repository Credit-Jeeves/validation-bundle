<?php
namespace RentJeeves\CoreBundle\Traits;

use Symfony\Component\HttpFoundation\Request;

trait MobileAware
{
    /**
     * The method define mobile user agent
     *
     * @param Request|null $request
     * @return bool
     */
    public function isMobile(Request $request = null)
    {
        if ($request && $request->headers->get('User-Agent')) {
            $userAgent = strtolower($request->headers->get('User-Agent'));
            $commonPhones="/phone|iphone|itouch|ipod|symbian|android|htc_|htc-";
            $commonOrganizersAndBrowsers="|palmos|blackberry|opera mini|iemobile|windows ce|";
            $uncommonDevices="nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/";
            if (preg_match($commonPhones.$commonOrganizersAndBrowsers.$uncommonDevices, $userAgent)) {
                return true;
            }
        }

        return false;
    }
}
