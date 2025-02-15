<?php

namespace Pixelant\PxaSocialFeed\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Pixelant\PxaSocialFeed\Domain\Model\Token;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * The repository for Feeds
 */
class TokenRepository extends AbstractBackendRepository
{
    /**
     * @var array $defaultOrderings
     */
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING
    ];

    /**
     * Finds a facebook page token based on the parent token (user token) and the social id.
     *
     * @param Token  $token
     * @param string $fbSocialId
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<QueryResult>
     */
    public function findFacebookPageToken(Token $token, string $fbSocialId)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->logicalAnd([
                $query->equals('parentToken', $token->getParentToken()),
                $query->equals('fbSocialId', $fbSocialId)
            ])
        );

        $query->setLimit(1);

        return $query->execute();
    }

    /**
     * @param array<string, int|string> $pageToken
     */
    public function addPageToken(array $pageToken): void
    {
        $pageAccessToken = [
            'tstamp' => time(),
            'crdate' => time(),
            'type' => Token::FACEBOOK_PAGE,
        ] + $pageToken;

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_pxasocialfeed_domain_model_token')
            ->insert('tx_pxasocialfeed_domain_model_token', $pageAccessToken)
        ;
    }

    /**
     * @param int $tokenUid
     */
    public function removeAllPageTokensByParentToken(int $tokenUid): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_pxasocialfeed_domain_model_token')
            ->delete('tx_pxasocialfeed_domain_model_token', [
                'parent_token' => $tokenUid,
            ])
        ;
    }

    /**
     * @param int    $uid
     * @param string $accessToken
     */
    public function updateAccessToken(int $uid, string $accessToken): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_pxasocialfeed_domain_model_token')
            ->update(
                'tx_pxasocialfeed_domain_model_token',
                ['access_token' => (string) $accessToken],
                ['uid' => $uid],
                [\PDO::PARAM_STR]
            )
        ;
    }
}
