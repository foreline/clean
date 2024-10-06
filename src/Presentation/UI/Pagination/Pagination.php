<?php
declare(strict_types=1);

namespace Presentation\UI\Pagination;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Формирование постраничной навигации
 */
class Pagination
{
    /** @var int Количество элементов на странице */
    private int $pageSize = 25;
    
    /** @var int Всего элементов */
    private int $totalCount = 0;
    
    /** @var int Номер страницы. Если не задан, то рассчитывается автоматически по значению параметра $_GET['p'] */
    private int $pageNum;
    
    /** @var string URL страницы списка */
    private string $listPageUrl = '';
    
    /** @var string Параметры URL, которые необходимо включить в ссылку. Если не задан, то рассчитывается автоматически на основании массива $_GET */
    private string $urlParams;
    
    /** @var string Шаблон формирования ссылки, допускаются замены: #PAGE_NUM# (номер страницы). Например 'p/#PAGE_NUM# */
    private string $template = 'p/#PAGE_NUM#';
    
    /** @var int Количество отображаемых страниц в начале, середине и конце навигации */
    private int $range = 5;
    
    private int $lastPageNum = 1;
    
    /**
     *
     */
    public function __construct()
    {
        $request = Request::createFromGlobals();
        
        $this->pageNum = (int) $request->get('p', 1);
        //$this->urlParams = $request->query->all();
        
        $arUrlParams = isset($arFields['AR_URL_PARAMS']) && 0 < count((array)$arFields['AR_URL_PARAMS']) ? $arFields['AR_URL_PARAMS'] : $_GET;
        $urlParamsPrefix = ( !str_contains($this->getTemplate(), '/') ) ? '&' : '?';
        unset($arUrlParams['p']);
        $urlParams = http_build_query($arUrlParams);
        $urlParams = !empty($urlParams) ? $urlParamsPrefix . $urlParams : '';
        $this->urlParams = $urlParams;
    }
    
    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }
    
    /**
     * @param int $pageSize
     * @return Pagination
     * @throws InvalidArgumentException
     */
    public function setPageSize(int $pageSize): Pagination
    {
        if ( 0 > $pageSize ) {
            throw new InvalidArgumentException('Количество элементов на странице должно быть больше положительным');
        }
        $this->pageSize = $pageSize;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
    
    /**
     * @param int $totalCount
     * @return Pagination
     */
    public function setTotalCount(int $totalCount): Pagination
    {
        $this->totalCount = $totalCount;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getPageNum(): int
    {
        return $this->pageNum;
    }
    
    /**
     * @param int $pageNum
     * @return Pagination
     */
    public function setPageNum(int $pageNum): Pagination
    {
        $this->pageNum = $pageNum;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getListPageUrl(): string
    {
        return $this->listPageUrl;
    }
    
    /**
     * @param string $listPageUrl
     * @return Pagination
     */
    public function setListPageUrl(string $listPageUrl): Pagination
    {
        $this->listPageUrl = $listPageUrl;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getUrlParams(): string
    {
        return $this->urlParams;
    }
    
    /**
     * @param string $urlParams
     * @return Pagination
     */
    public function setUrlParams(string $urlParams): Pagination
    {
        $this->urlParams = $urlParams;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
    
    /**
     * @param string $template
     * @return Pagination
     */
    public function setTemplate(string $template): Pagination
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getRange(): int
    {
        return $this->range;
    }
    
    /**
     * @param int $range
     * @return Pagination
     */
    public function setRange(int $range): Pagination
    {
        $this->range = $range;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getLastPageNum(): int
    {
        return $this->lastPageNum;
    }
    
    /**
     * @param int $lastPageNum
     * @return Pagination
     */
    public function setLastPageNum(int $lastPageNum): Pagination
    {
        $this->lastPageNum = $lastPageNum;
        return $this;
    }
    
    /**
     * Формирует и возвращает постраничную навигацию
     *
     * @return string $navString
     */
    public function getNav(): string
    {
        $template = $this->getTemplate();
        
        if ( str_starts_with($template, '/') ) {
            $template = substr($template, 1);
        }
        $this->setTemplate($template);
        
        // Расчет параметров
        
        /** @var string $urlParamsPrefix Префикс для url-параметров. Если шаблон содержит "/", то скорее всего это ЧПУ - используется "?". */
        //$urlParamsPrefix = ( !str_contains($template, '/') ) ? '&' : '?';
        
        /** @var int $lastPageNum Номер последней страницы (количество страниц) */
        if ( 0 >= $this->getPageSize() ) {
            $this->setLastPageNum(1); // @fixme уточнить номер последней страницы при нулевом количестве размера страницы
        } else {
            $this->setLastPageNum((int) ceil($this->getTotalCount() / $this->getPageSize()));
            
        }
        
        // @fixme
        //unset($arUrlParams['p']);
        //            $urlParams = http_build_query($arUrlParams);
        //            $urlParams = !empty($urlParams) ? $urlParamsPrefix . $urlParams : '';
        //            $this->setUrlParams($urlParams);
        
        // Не отображаем постраничную навигацию
        if ( $this->getPageSize() >= $this->getTotalCount() ) {
            return '';
        }
        
        // Формирование постраничной навигации
        $nav = '<nav>';
        
        $nav .= '<ul class="pagination">';
        
        // В начало
        $nav .= '
        <li class="page-item ' . (1 === $this->getPageNum() ? 'disabled' : '') . '">
            <a class="page-link has-ripple" data-page-num="1" href="' . $this->getListPageUrl() . $this->getUrlParams() . '" aria-label="в начало">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        ';
        
        // Предыдущая страница
        $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)($this->getPageNum() - 1), $this->getTemplate()) . $this->getUrlParams();
        
        $nav .= '
        <li class="page-item ' . (1 === $this->getPageNum() ? 'disabled' : '') . '">
            <a class="page-link has-ripple" data-page-num="' . ($this->getPageNum() - 1) . '" href="' . $url . '" aria-label="предыдущая">
                <span aria-hidden="true">&lsaquo;</span>
            </a>
        </li>
        ';
        
        // Начальный блок
        $nav .= $this->getStartBlock();
        
        // Средний блок
        $nav .= $this->getMiddleBlock();
        
        // Конечный блок
        $nav .= $this->getEndBlock();
        
        // Следующая страница
        $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)($this->getPageNum() + 1), $this->getTemplate()) . $this->getUrlParams();
        $nav .= '
        <li class="page-item ' . ($lastPageNum === $this->getPageNum() ? 'disabled' : '') . '">
            <a class="page-link has-ripple" data-page-num="' . ($this->getPageNum() + 1) . '" href="' . $url . '" aria-label="следующая">
                <span aria-hidden="true">&rsaquo;</span>
            </a>
        </li>
        ';
        
        // В конец
        $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)$lastPageNum, $this->getTemplate()) . $this->getUrlParams();
        $nav .= '
        <li class="page-item ' . ($lastPageNum === $this->getPageNum() ? 'disabled' : '') . '">
            <a class="page-link has-ripple" data-page-num="' . $lastPageNum . '" href="' . $url . '" aria-label="в конец">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
        ';
        
        $nav .= '</ul>';
        $nav .= '</nav>';
        
        return $nav;
    }
    
    /**
     * @return string
     */
    private function getStartBlock(): string
    {
        $block = '';
        
        if ( $this->getLastPageNum() < $this->getRange() ) {
            $np = $this->getLastPageNum();
        } else {
            $np = (4 + floor($this->getRange() / 2) > $this->getPageNum() ? $this->getRange() + ($this->getPageNum() - 1) : $this->getRange());
        }
        
        if ( $np > $this->getLastPageNum() ) {
            $np = $this->getLastPageNum();
        }
        
        for ( $i = 1; $i <= $np; $i++ ) {
            $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)$i, $this->getTemplate()) . $this->getUrlParams();
            $block .= '<li class="page-item' . ((int)$i === $this->getPageNum() ? ' active ' : '') . '" ' . ($i === $this->getPageNum() ? ' aria-current="page" ' : '') . '>';
            $block .= '<a class="page-link has-ripple" data-page-num="' . $i . '" href="' . $url . '">' . $i . '</a>';
            $block .= '</li>';
        }
        
        return $block;
    }
    
    /**
     * @return string
     */
    private function getMiddleBlock(): string
    {
        $block = '';
        
        if ( $this->getPageNum() <= ($this->getLastPageNum() - $this->getRange()) && (4 + floor($this->getRange() / 2)) <= $this->getPageNum() ) {
            
            if ( ($this->getRange() + ceil($this->getRange() / 2)) < $this->getPageNum() ) {
                $block .= '<li class="page-item disabled"><a class="page-link has-ripple" href="#">&hellip;</a></li>';
            }
            
            for ( $i = ($this->getPageNum() - floor($this->getRange() / 2)); $i <= ($this->getPageNum() + floor($this->getRange() / 2)); $i++ ) {
                
                if ( $this->getRange() >= $i ) {
                    continue;
                }
                
                if ($i > ($this->getLastPageNum() - $this->getRange())) {
                    continue;
                }
                
                $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)$i, $this->getTemplate()) . $this->getUrlParams();
                
                $block .= '<li class="page-item' . ((int)$i === $this->getPageNum() ? ' active ' : '') . '" ' . ($i === $this->getPageNum() ? ' aria-current="page" ' : '') . '>';
                $block .= '<a class="page-link has-ripple" data-page-num="' . $i . '" href="' . $url . '">' . $i . '</a>';
                $block .= '</li>';
            }
            
            if ( ($this->getPageNum() + floor($this->getRange() / 2)) < ($this->getLastPageNum() - $this->getRange()) ) {
                $block .= '<li class="page-item disabled"><a class="page-link has-ripple" href="#">&hellip;</a></li>';
            }
            
        } elseif ($this->getLastPageNum() > $this->getRange()) {
            $block .= '<li class="page-item disabled"><a class="page-link has-ripple" href="#">&hellip;</a></li>';
        }
        
        return $block;
    }
    
    /**
     * @return string
     */
    private function getEndBlock(): string
    {
        $block = '';
        
        if ( (2 * $this->getRange()) <= $this->getLastPageNum() ) {
            
            $sp = ($this->getLastPageNum() - ($this->getRange() - 1));
            
            if ( ($this->getPageNum() > $sp - ceil($this->getRange() / 2)) && ($this->getPageNum() > ($this->getLastPageNum() - $this->getRange())) ) {
                $sp = $this->getPageNum() - ceil($this->getRange() / 2);
            }
            
            for ($i = $sp; $i <= $this->getLastPageNum(); $i++) {
                $url = $this->getListPageUrl() . str_replace('#PAGE_NUM#', (string)$i, $this->getTemplate()) . $this->getUrlParams();
                $block .= '<li class="page-item' . ((int)$i === $this->getPageNum() ? ' active ' : '') . '" ' . ($i === $this->getPageNum() ? ' aria-current="page" ' : '') . '>';
                $block .= '<a class="page-link has-ripple" data-page-num="' . $i . '" href="' . $url . '">' . $i . '</a>';
                $block .= '</li>';
            }
        }
        
        return $block;
    }
    
}