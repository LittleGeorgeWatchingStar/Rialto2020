<?php

namespace Rialto\Web;

use Exception;
use Rialto\Alert\AlertMessage;
use Rialto\Alert\AlertResolution;
use Rialto\Alert\LinkResolution;
use Rialto\Company\Company;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Database\RecordList;
use Rialto\Util\Strings\TextFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig_Filter;
use Twig_Function;

/**
 * Core Twig extensions.
 */
class RialtoExtension extends AbstractExtension implements GlobalsInterface
{
    use TwigExtensionTrait;

    /** @var RouterInterface */
    private $router;

    /** @var DoctrineDbManager */
    private $dbm;

    public function __construct(RouterInterface $router,
                                DoctrineDbManager $dbm)
    {
        $this->router = $router;
        $this->dbm = $dbm;
    }

    public function getGlobals()
    {
        $company = Company::getProxy($this->dbm->getEntityManager());
        return [
            'company' => $company,
            'companyName' => $company->getShortName(),
        ];
    }

    /**
     * Eg. {{ value|filter }}
     */
    public function getFilters()
    {
        return [
            $this->simpleFilter('camelToWords', 'camelToWords', []),
            new Twig_Filter('lcfirst', 'lcfirst'),
            new Twig_Filter('ucfirst', 'ucfirst'),
            $this->simpleFilter('rialto_tweak_uri', 'tweakUri', []),
            $this->simpleFilter('log_context', 'logContext', ['html']),
            $this->simpleFilter('alert_resolution', 'alertResolution', ['html']),
            $this->simpleFilter('url_timestamp', 'urlTimestamp', ['html']),
            $this->simpleFilter('sort_field', 'sortByField', []),
            $this->simpleFilter('rialto_entity_sort_link', 'sortLink', ['html']),
            $this->simpleFilter('url_host', 'urlHost', ['html']),
            $this->simpleFilter('or_none', 'valueOrNone', ['html']),
        ];
    }

    /**
     * Eg. {{ function(value) }}
     */
    public function getFunctions()
    {
        return [
            $this->simpleFunction('htmlLink', 'htmlLink', ['html']),
            $this->simpleFunction('rialto_alert', 'alert', ['html']),
            new Twig_Function('get_class', 'get_class'),
            $this->simpleFunction('yes_no_any', 'yesNoAny', ['html']),
            $this->simpleFunction('record_count', 'recordCount', ['html']),
        ];
    }

    public function camelToWords($text)
    {
        $formatter = new TextFormatter();
        return $formatter->camelToWords($text);
    }

    public function alert($message)
    {
        if ($message instanceof AlertMessage) {
            return $this->alertMessage($message);
        }
        return $message;
    }

    private function alertMessage(AlertMessage $alert)
    {
        try {
            $string = htmlspecialchars($alert->getMessage(), null, 'UTF-8');
            $resolution = $alert->getResolution();
            if ($resolution) {
                $string .= $this->alertResolution($resolution);
            }
            return $string;
        } catch (Exception $ex) {
            error_log($ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
            return $ex->getMessage();
        }
    }

    public function alertResolution(AlertResolution $resolution)
    {
        if ($resolution instanceof LinkResolution) {
            return $this->htmlLink($resolution->getText(), $resolution->getUri());
        } else {
            return $resolution->getText();
        }
    }

    /**
     * Eg: "Showing 100 out of 1,950 matching records."
     */
    public function recordCount($list)
    {
        $msg = ($list instanceof RecordList)
            ? sprintf('%s of %s matching ',
                $list->limit() ? number_format($list->limit()) : 'all',
                number_format($list->total()))
            : number_format(count($list));
        return sprintf(
            '<span class="record-count">Showing %s records.</span>',
            $msg);
    }

    /**
     * Formats a date for use in a URL or query string parameter.
     */
    public function urlTimestamp(\DateTime $date = null)
    {
        return null === $date ? null : $date->format('YmdHis');
    }

    public function htmlLink($label, $uri)
    {
        return sprintf('<a href="%s">%s</a>', $uri, htmlentities($label));
    }

    /**
     * @param Request $request
     *  The current request
     * @param array $query
     *  An array of any query string parameters you want to modify
     * @return string
     *  The new URI.
     */
    public function tweakUri(Request $request, array $query = [])
    {
        $oldQuery = $request->query->all();
        $newQuery = array_merge($oldQuery, $query);
        $newQueryString = http_build_query($newQuery);
        $newRequest = $request->duplicate($newQuery);
        $newRequest->server->set('QUERY_STRING', $newQueryString);
        return $newRequest->getUri();
    }

    /**
     * Recursively renders the context of a log entry.
     */
    public function logContext(array $context)
    {
        $html = '<ul class="context">';
        foreach ($context as $key => $value) {
            if ($key === 'user') continue;
            if (empty($value)) continue;
            $html .= "<li>" . htmlspecialchars($key) . ': ';
            if (is_array($value)) {
                $html .= $this->logContext($value);
            } else {
                $html .= htmlspecialchars($value);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function yesNoAny($name, Request $request = null)
    {
        $value = $request ? $request->get($name) : null;
        $html = "<select name=\"$name\" id=\"$name\">";
        foreach (['any', 'yes', 'no'] as $option) {
            $selected = ($value == $option) ? 'selected' : '';
            $html .= "<option value=\"$option\" $selected>$option</option>";
        }
        $html .= "</select>";
        return $html;
    }

    /**
     * Sort a list of objects by the field whose name is given.
     *
     * Works with getter methods, too.
     */
    public function sortByField(array $objects, $field)
    {
        $acc = PropertyAccess::createPropertyAccessor();
        usort($objects, function ($a, $b) use ($acc, $field) {
            $av = $acc->getValue($a, $field);
            $bv = $acc->getValue($b, $field);
            return ($av == $bv) ? 0 : ($av < $bv ? -1 : 1);
        });
        return $objects;
    }

    /**
     * Sets the _order query string parameter to the value of $sortField and
     * returns the resulting URI.
     *
     * The _order parameter is the one used by FilterQueryBuilder to
     * set the sort order of a record set.
     *
     * @see FilterQueryBuilder
     * @param Request $request
     * @param string $sortField
     * @return string The modified URI
     */
    public function sortLink(Request $request, $sortField)
    {
        $params = ['_order' => $sortField];
        return $this->tweakUri($request, $params);
    }

    /**
     * Returns just the hostname part of a url.
     *
     * @param string $url
     * @return string
     */
    public function urlHost($url)
    {
        $url = trim($url);
        if (!$url) {
            return '';
        }
        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * @returns string the escaped value, or a "null" span.
     */
    public function valueOrNone($value)
    {
        return $value ? htmlentities($value) : $this->none();
    }
}
