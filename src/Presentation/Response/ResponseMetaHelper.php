<?php
declare(strict_types=1);

namespace Presentation\Response;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 *
 */
class ResponseMetaHelper
{
    /**
     * @param int $pageNum
     * @param int $pageSize
     * @param int $totalCount
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getMeta(int $pageNum = 1, int $pageSize = 25, int $totalCount = 0): array
    {
        //Assert::greaterThan($pageSize, 0, 'Page size must be greater than 0');
        //Assert::greaterThanEq($pageNum, 0, 'Page number must be positive integer');
        //Assert::greaterThanEq($totalCount, 0, 'Total count must be positive integer');
        
        return [
            'pagination'    => [
                'page'      => $pageNum,
                'pageSize'  => $pageSize,
                'pageCount' => ( 0 < $pageSize ? ceil($totalCount / $pageSize) : 0 ),
            ],
            'totalCount'    => $totalCount,
        ];
    }
}