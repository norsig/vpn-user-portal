<?php
/**
 *  Copyright (C) 2016 SURFnet.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SURFnet\VPN\Portal;

use SURFnet\VPN\Common\Http\BeforeHookInterface;
use SURFnet\VPN\Common\Http\Exception\HttpException;
use SURFnet\VPN\Common\Http\RedirectResponse;
use SURFnet\VPN\Common\Http\Request;
use SURFnet\VPN\Common\Http\SessionInterface;

/**
 * This hook is used to be able to switch the language without requiring to be
 * authenticated. As the language switcher is only a user preference stored in
 * the user's session this is not a problem. This way, even the authentication
 * page can use the language switcher.
 */
class LanguageSwitcherHook implements BeforeHookInterface
{
    /** @var \SURFnet\VPN\Common\Http\SessionInterface */
    private $session;

    /** @var array */
    private $supportedLanguages;

    public function __construct(SessionInterface $session, array $supportedLanguages)
    {
        $this->session = $session;
        $this->supportedLanguages = $supportedLanguages;
    }

    public function executeBefore(Request $request, array $hookData)
    {
        if ('POST' !== $request->getRequestMethod()) {
            return false;
        }

        if ('/setLanguage' !== $request->getPathInfo()) {
            return false;
        }

        $language = $request->getPostParameter('setLanguage', false, 'en_US');
        if (!in_array($language, $this->supportedLanguages)) {
            throw new HttpException('invalid language', 400);
        }

        $this->session->set('activeLanguage', $language);

        // XXX do we need to validate HTTP_REFERER here?
        return new RedirectResponse($request->getHeader('HTTP_REFERER'), 302);
    }
}
