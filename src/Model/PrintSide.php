<?php

namespace App\Model;

use App\Entity\FuelReconciliation;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Project;
use App\Repository\FuelReconciliationRepository;

class PrintSide
{
    private $projects;
    private $totalAmount = 0;
    private $totalLiters = 0;
    private $subtotals = array();
    private $subLiterstotals = array();
    private $startDate;
    private $endDate;
    private $gasStation;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->startDate = new \DateTime();
        $this->endDate = new \DateTime();
    }

    public function addReconciliations($reconciliations)
    {
        $project = new Project();
        $this->gasStation = $reconciliations[0]->getGasStation()->getName();
        $project->setId(rand(1, 10000));
        foreach ($reconciliations as $reconciliation) {
            $this->addReconciliation($reconciliation, $project);
        }
        $this->projects->add($project);

        return $this;
    }

    public function addReconciliation(FuelReconciliation $reconciliation, Project $project)
    {
        $project->addFuelReconciliation($reconciliation);
        $this->subtotals[$project->getId()] = key_exists($project->getId(), $this->subtotals) ? $this->subtotals[$project->getId()] : 0;
        $this->subLiterstotals[$project->getId()] = key_exists($project->getId(), $this->subLiterstotals) ? $this->subLiterstotals[$project->getId()] : 0;
        $this->subtotals[$project->getId()] = $this->subtotals[$project->getId()] + $reconciliation->getTotalAmount();
        $this->subLiterstotals[$project->getId()] = $this->subLiterstotals[$project->getId()] + $reconciliation->getTotalLitres();
        $this->totalAmount = $this->totalAmount + $reconciliation->getTotalAmount();
        $this->totalLiters = $this->totalLiters + $reconciliation->getTotalLitres();

        return $project;
    }

    /**
     * Remove Project.
     *
     * @return $this
     *
     * @param \App\Entity\Project $project
     */
    public function removeProject($project)
    {
        $this->projects = $this->projects->filter(function ($_project) use ($project) {
            return $_project->getId() !== $project;
        });
        $this->totalAmount -= $this->subtotals[$project];
        $this->totalLiters -= $this->subLiterstotals[$project];
        unset($this->subLiterstotals[$project]);
        unset($this->subtotals[$project]);

        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAllReconciliations()
    {
        $reconciliations = array();
        foreach ($this->projects->toArray() as $project) {
            foreach ($project->getFuelReconciliations() as $reconciliation) {
                $reconciliations[] = $reconciliation->getId();
            }
        }

        return $reconciliations;
    }

    public function refreshFromDatabase(FuelReconciliationRepository $recociliationRepo)
    {
        $clone = new PrintSide();
        $clone->totalAmount = $this->totalAmount;
        $clone->totalLiters = $this->totalLiters;
        $clone->setGasStation($this->getGasStation());
        $clone->subLiterstotals = $this->subLiterstotals;
        $clone->setSubTotals($this->getSubTotals());
        foreach ($this->getProjects()->toArray() as $project) {
            $cloneProject = clone $project;
            // $cloneProject->setFuelReconciliations(array());
            foreach ($project->getFuelReconciliations() as $reconciliation) {
                $cloneReconciliation = $recociliationRepo->find($reconciliation->getId());
                $cloneProject->addFuelReconciliation($cloneReconciliation);
            }
            $clone->addProject($cloneProject, false);
        }

        return $clone;
    }

    public function getSubTotals()
    {
        return $this->subtotals;
    }

    public function setSubTotals($subTotals)
    {
        $this->subtotals = $subTotals;

        return $this;
    }

    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add Project.
     *
     * @param \App\Entity\Project $project
     *
     * @return Project
     */
    public function addProject(Project $project, $cleanReconciliations = true)
    {
        if ($cleanReconciliations) {
            $project->setFuelReconciliations(array());
        }
        $this->projects->add($project);

        return $this;
    }

    public function getSubLitersTotals()
    {
        return $this->subLiterstotals;
    }

    public function setSubLitersTotals($subLitersTotals)
    {
        $this->subLiterstotals = $subLitersTotals;

        return $this;
    }

    public function sortDates()
    {
        if ($this->projects->count() === 0 || count($this->projects->first()->getFuelReconciliations()) === 0) {
            return;
        }
        $startDate = $this->projects->first()->getFuelReconciliations()[0]->getCreatedAt();
        $endDate = $this->projects->first()->getFuelReconciliations()[0]->getCreatedAt();
        foreach ($this->projects as $project) {
            foreach ($project->getFuelReconciliations() as $reconciliation) {
                if ($reconciliation->getCreatedAt() > $endDate) {
                    $endDate = $reconciliation->getCreatedAt();
                }
                if ($reconciliation->getCreatedAt() < $startDate) {
                    $startDate = $reconciliation->getCreatedAt();
                }
            }
        }
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function removeReconciliation(FuelReconciliation $reconciliation)
    {
        foreach ($this->projects->toArray() as $project) {
            for ($i = 0; $i < count($project->getFuelReconciliations()); ++$i) {
                if ($project->getFuelReconciliations()[$i]->getId() === $reconciliation->getId()) {
                    $_reconciliation = $project->getFuelReconciliations()[$i];
                    unset($project->getFuelReconciliations()[$i]);
                    $this->subtotals[$project->getId()] = $this->subtotals[$project->getId()] - $_reconciliation->getTotalAmount();
                    $this->subLiterstotals[$project->getId()] = $this->subLiterstotals[$project->getId()] - $_reconciliation->getTotalLiters();
                    $this->totalAmount = $this->totalAmount - $_reconciliation->getTotalAmount();
                    $this->totalLiters = $this->totalLiters - $_reconciliation->getTotalLiters();
                }
            }
        }

        return $this;
    }

    public function exportToExcel()
    {
        return 'nothing to show';
    }

    public function getProjectEarlierReconciliation(Project $project)
    {
        $startDate = $project->getFuelReconciliations()[0]->getCreatedAt();
        foreach ($project->getFuelReconciliations() as $reconciliation) {
            if ($reconciliation->getCreatedAt() < $startDate) {
                $startDate = $reconciliation->getCreatedAt();
            }
        }

        return $startDate;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param int $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return int
     */
    public function getTotalLiters()
    {
        return $this->totalLiters;
    }

    /**
     * @param int $totalLiters
     *
     * @return $this
     */
    public function setTotalLiters($totalLiters)
    {
        $this->totalLiters = $totalLiters;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGasStation()
    {
        return $this->gasStation;
    }

    /**
     * @param mixed $gasStation
     *
     * @return PrintSide
     */
    public function setGasStation($gasStation)
    {
        $this->gasStation = $gasStation;

        return $this;
    }

    public function getGenericProjectName()
    {
        if ($this->getProjects()->count() > 0 && count($this->getProjects()->first()->getFuelReconciliations()) > 0) {
            return $this->getProjects()->first()->getFuelReconcReconciliations()[0]->getProject()->getName();
        }
    }
}
