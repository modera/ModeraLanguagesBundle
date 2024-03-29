<?php

namespace Modera\LanguagesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;

/**
 * @ORM\Entity
 * @ORM\Table(name="modera_languages_language", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="locale", columns={"locale"})
 * })
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Language
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $locale;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isEnabled = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isDefault = false;

    /**
     * @deprecated Use native ::class property
     *
     * @return string
     */
    public static function clazz()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return get_called_class();
    }

    /**
     * Returns the name of a locale.
     *
     * @param string      $locale
     * @param null|string $displayLocale
     *
     * @return string
     */
    public static function getLocaleName($locale, $displayLocale = null)
    {
        $str = null;
        try {
            $str = Locales::getName($locale, $displayLocale ?: $locale);
        } catch (MissingResourceException $e) {}

        if (!$str) {
            $parts = explode('_', $locale);
            if (count($parts) > 1) {
                $code = array_pop($parts);
                $country = $code;
                try {
                    $country = Countries::getName($code, $displayLocale ?: $parts[0]);
                } catch (MissingResourceException $e) {}
                while (count($parts) && !$str) {
                    $value = implode('_', $parts);
                    $str = null;
                    try {
                        $str = Locales::getName($value, $displayLocale ?: $value);
                    } catch (MissingResourceException $e) {}
                    array_pop($parts);
                }

                if ($str) {
                    if (')' === substr($str, -1)) {
                        $str = substr($str, 0, -1) . ', ' . $country . ')';
                    } else {
                        $str .= ' (' . $country . ')';
                    }
                }
            }
        }

        $enc = 'utf-8';
        $name = $str ?: $locale;

        return mb_strtoupper(mb_substr($name, 0, 1, $enc), $enc).mb_substr($name, 1, mb_strlen($name, $enc), $enc);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|string $displayLocale
     * @return string
     */
    public function getName($displayLocale = null)
    {
        return static::getLocaleName($this->locale, $displayLocale);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * For ModeraServerCrudBundle.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @deprecated  Since v3.1.0, use Language::isEnabled() method.
     * @return bool
     */
    public function getEnabled()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use Language::isEnabled() method.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->isEnabled;
    }

    /**
     * @param bool $status
     */
    public function setEnabled($status)
    {
        $this->isEnabled = $status;
    }

    /**
     * For ModeraServerCrudBundle.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault();
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param bool $status
     */
    public function setDefault($status)
    {
        $this->isDefault = $status;
    }
}
