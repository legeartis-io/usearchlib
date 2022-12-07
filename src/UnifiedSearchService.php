<?php

namespace Legeartis\UnifiedSearch;

use GuzzleHttp\Exception\GuzzleException;
use Legeartis\UnifiedSearch\responseObjects\CountByTagsList;
use Legeartis\UnifiedSearch\responseObjects\DetailsByOemList;
use Legeartis\UnifiedSearch\responseObjects\OriginalDetailsList;
use Legeartis\UnifiedSearch\responseObjects\QueryCompletions;
use Legeartis\UnifiedSearch\responseObjects\SearchResult;
use Legeartis\UnifiedSearch\responseObjects\Task;
use Legeartis\UnifiedSearch\responseObjects\TaskList;
use Legeartis\UnifiedSearch\responseObjects\UserInfo;
use Legeartis\UnifiedSearch\responseObjects\Vehicle;
use Legeartis\UnifiedSearch\responseObjects\VehicleList;

/**
 * Class UnifiedSearchService
 * @package Legeartis\UnifiedSearch
 */
class UnifiedSearchService extends Service
{
    /*****************************
     *  Identify vehicle API
     *****************************/

    /**
     * Get possible vehicle modifications for submitted VIN or Frame number. Return previous result in last 24 hours if any exists or perform new identification
     * @param string $vin Vehicle VIN
     * @param string $locale Locale
     * @return VehicleList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function identify(string $vin, string $locale = 'ru_RU'): VehicleList
    {
        return $this->makeRequest(VehicleList::class, 'GET', 'identify', '', null, [
            'vin' => $vin,
            'locale' => $locale,
        ]);
    }

    /*****************************
     *  Index vehicle API
     *****************************/

    /**
     * List indexed vehicles
     * @param string $indexedVehicleId
     * @return VehicleList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function getIndexedVehicles(int $skip = 0, int $limit = 20): VehicleList
    {
        return $this->makeRequest(VehicleList::class, 'GET', 'indexedVehicles', '', '', [
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Start vehicle indexation by catalog/vehicleId/ssd from identified model
     * @param string $catalog Laximo.OEM catalog code from identify method
     * @param string $ssd Laximo.OEM vehicle ssd from identify method
     * @param string $vehicleId Laximo.OEM vehicleId from identify method
     * @param string $locale Locale
     * @return Vehicle
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function indexVin(string $catalog, string $ssd, string $vehicleId, string $locale = 'ru_RU'): Vehicle
    {
        return $this->makeRequest(Vehicle::class, 'POST', 'indexedVehicles', '', null, [
            'vehicleId' => $vehicleId,
            'ssd' => $ssd,
            'catalog' => $catalog,
            'locale' => $locale,
        ]);
    }

    /**
     * get vehicle indexation progress
     * @param string $indexedVehicleId
     * @return VehicleList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function getVehicle(string $indexedVehicleId): Vehicle
    {
        return $this->makeRequest(Vehicle::class, 'GET', 'indexedVehicles', '', $indexedVehicleId);
    }

    /*****************************
     *  Offers Processing API
     *****************************/

    /**
     * Cancel running upload
     * @param int $taskId
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function cancelTask(int $taskId): void
    {
        $this->makeRequest(null, 'GET', 'offers', 'cancel', $taskId);
    }

    /**
     * download errors file
     * @param int $taskId
     * @return string
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function downloadErrors(int $taskId): string
    {
        return $this->makeRequest(null, 'GET', 'offers', 'downloadErrors', $taskId);
    }

    /**
     * download source file
     * @param int $taskId
     * @return string
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function downloadSource(int $taskId): string
    {
        return $this->makeRequest(null, 'GET', 'offers', 'downloadSource', $taskId);
    }

    /**
     * Get task data
     * @param int $taskId
     * @return Task
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function getTaskById(int $taskId): Task
    {
        return $this->makeRequest(Task::class, 'GET', 'offers', 'get', $taskId);
    }

    public const TASK_STATUS_QUEUED = 'QUEUED';
    public const TASK_STATUS_RUNNING = 'RUNNING';
    public const TASK_STATUS_FINISHED = 'FINISHED';
    public const TASK_STATUS_ABORTED = 'ABORTED';
    public const TASK_STATUS_FAILED = 'FAILED';

    /**
     * @param int $skip
     * @param int $limit
     * @param string|null $taskStatus TASK_STATUS_* constant
     * @return TaskList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function getListTasks(int $skip = 0, int $limit = 10, ?string $taskStatus = null): TaskList
    {
        $params = ['skip' => $skip, 'limit' => $limit];
        if ($taskStatus) {
            $params['taskStatus'] = $taskStatus;
        }

        return $this->makeRequest(TaskList::class, 'GET', 'offers', 'listTasks', null, $params);
    }

    /**
     * Upload new file with offers, tags and original details crosses. Accepts tab separated (UTF-16 BOM (FEFF) lead) or csv (UTF-8) file. Zip archives containing single file with appropriate format supported.
     * @param string $filePath
     * @param string $fileName
     * @return Task
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function uploadFile(string $filePath, string $fileName): Task
    {
        return $this->makeRequest(Task::class, 'POST', 'offers', 'upload', null, [], false, null, $filePath, $fileName);
    }

    /*****************************
     *  Search API
     *****************************/

    public const SEARCH_OPTION_CONSIDER_CROSSES = 'considerCrosses';
    public const SEARCH_OPTION_IN_STOCK = 'inStock';
    public const SEARCH_OPTION_WITH_OFFERS = 'withOffers';
    public const SEARCH_TAGS_BY_AND = 'tagsByAnd';

    /**
     * Search details with free-form (natural language) full text query.If indexedAutoId provided search only applicable details.
     * @param string $query user query, including VIN
     * @param string[] $tags array of user provided tag names
     * @param string[] $options array of SEARCH_OPTION_*, the default is SEARCH_OPTION_CONSIDER_CROSSES + SEARCH_OPTION_IN_STOCK + SEARCH_OPTION_WITH_OFFERS
     * @param int $skip
     * @param int $limit
     * @return SearchResult
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function search(string $query, array $tags = [], array $options = [], int $skip = 0, int $limit = 10, int $indexedAutoId = 0): SearchResult
    {
        if (!$options) {
            $options = [
                self::SEARCH_OPTION_CONSIDER_CROSSES,
                self::SEARCH_OPTION_IN_STOCK,
                self::SEARCH_OPTION_WITH_OFFERS,
            ];
        }
        $searchParams = [
            'limit' => $limit,
            'skip' => $skip,
            'considerCrosses' => in_array(self::SEARCH_OPTION_CONSIDER_CROSSES, $options) ? 'true' : 'false',
            'inStock' => in_array(self::SEARCH_OPTION_IN_STOCK, $options) ? 'true' : 'false',
            'withOffers' => in_array(self::SEARCH_OPTION_WITH_OFFERS, $options) ? 'true' : 'false',
            'tagsByAnd' => in_array(self::SEARCH_TAGS_BY_AND, $options) ? 'true' : 'false',
            'query' => $query
        ];

        if ($indexedAutoId) {
            $searchParams['indexedAutoId'] = $indexedAutoId;
        }

        if ($tags) {
            $searchParams['tags'] = $tags;
        }

            return $this->makeRequest(SearchResult::class, 'GET', 'search', '', null, $searchParams);
    }

    /**
     * Get possible completions for incomplete query
     * @param string $query incomplete search query
     * @return QueryCompletions
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function completeQuery(string $query): QueryCompletions
    {
        $params['incompleteQuery'] = $query;

        return $this->makeRequest(QueryCompletions::class, 'GET', 'search', 'completeQuery', null, $params);
    }

    /**
     * Get counts of applicable details grouped by tags for Vehicle
     * @param int $indexedAutoId Indexed vehicle id
     * @return CountByTagsList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function countByTags(int $indexedAutoId): CountByTagsList
    {
        return $this->makeRequest(CountByTagsList::class, 'GET', 'countByTags', '', null, ['indexedAutoId' => $indexedAutoId]);
    }

    /**
     * Search details and replacements from offers file by original Catalog OEMs applicable to Vehicle
     * @param int $indexedAutoId Indexed vehicle id
     * @param string[] $oems OEMS list
     * @param string $vin
     * @param bool $withOffers with offers
     * @param false $inStock fetch in stocky only
     * @return DetailsByOemList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function detailsByCatalogOems(int $indexedAutoId, array $oems, bool $withOffers = false, $inStock = false): DetailsByOemList
    {
        $body = [
            'indexedAutoId' => $indexedAutoId,
            'oems' => $oems,
            'withOffers' => $withOffers,
            'inStock' => $inStock,
        ];
        return $this->makeRequest(DetailsByOemList::class, 'POST', 'search', 'detailsByCatalogOems', null, $body, false);
    }

    /**
     * Get original details for passed replacement detail id for Vehicle with indexedAutoId
     * @param string $indexedAutoId
     * @param string $detailId
     * @return OriginalDetailsList
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function originalApplicableDetails(int $indexedAutoId, int $detailId): OriginalDetailsList
    {
        $params = [
            'indexedAutoId' => $indexedAutoId,
            'detailId' => $detailId,
        ];

        return $this->makeRequest(OriginalDetailsList::class, 'GET', 'search', 'originalApplicableDetails', null, $params);
    }

    /*****************************
     *  User Info API
     *****************************/

    /**
     * get current user
     * @return UserInfo
     * @throws GuzzleException
     * @throws exceptions\AccessDeniedException
     * @throws exceptions\BadRequestException
     * @throws exceptions\ExecutionException
     * @throws exceptions\NotFoundException
     * @throws exceptions\USException
     */
    public function getUserInfo(): UserInfo
    {
        return $this->makeRequest(UserInfo::class, 'GET', 'user');
    }
}
