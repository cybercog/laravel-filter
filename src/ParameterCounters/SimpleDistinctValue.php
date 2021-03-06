<?php
namespace Czim\Filter\ParameterCounters;

use Czim\Filter\Contracts\CountableFilterInterface;
use Czim\Filter\Contracts\ParameterCounterInterface;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Counts different distinct values for a single column with configurable aliases.
 */
class SimpleDistinctValue implements ParameterCounterInterface
{
    /**
     * @var string
     */
    protected $columnName;

    /**
     * @var bool
     */
    protected $includeEmpty;

    /**
     * @var string
     */
    protected $countRaw;

    /**
     * @var string
     */
    protected $columnAlias;

    /**
     * @var string
     */
    protected $countAlias;

    /**
     * @param null   $columnName        the column name to count
     * @param bool   $includeEmpty      whether to also count for NULL, default is to exclude
     * @param string $countRaw          the raw SQL count statement ('COUNT(*)')
     * @param string $columnAlias       an alias for the column ('id')
     * @param string $countAlias        an alias for the count ('count')
     */
    public function __construct($columnName = null, $includeEmpty = false, $countRaw = 'COUNT(*)', $columnAlias = 'value', $countAlias = 'count')
    {
        $this->columnName   = $columnName;
        $this->includeEmpty = $includeEmpty;
        $this->countRaw     = $countRaw;
        $this->columnAlias  = $columnAlias;
        $this->countAlias   = $countAlias;
    }

    /**
     * Returns the count for a countable parameter, given the query provided
     *
     * @param string                   $name
     * @param EloquentBuilder          $query
     * @param CountableFilterInterface $filter
     * @return array
     */
    public function count($name, $query, CountableFilterInterface $filter)
    {
        // if the columnname is not set, assume an id field based on a table name
        $columnName  = (empty($this->columnName))
                            ?   $name
                            :   $this->columnName;

        if ( ! $this->includeEmpty) {
            $query->whereNotNull($columnName);
        }

        return $query->select(
                        "{$columnName} AS {$this->columnAlias}",
                        DB::raw("{$this->countRaw} AS {$this->countAlias}")
                     )
                     ->groupBy($columnName)
                     ->lists($this->countAlias, $this->columnAlias);
    }
}
