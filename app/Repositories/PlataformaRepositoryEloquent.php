<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\PlataformaRepository;
use Plataforma;
use App\Validators\PlataformaValidator;

/**
 * Class PlataformaRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class PlataformaRepositoryEloquent extends BaseRepository implements PlataformaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Plataforma::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
