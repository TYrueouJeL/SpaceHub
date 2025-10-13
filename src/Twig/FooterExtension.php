<?php
namespace App\Twig;

use App\Repository\PlaceTypeRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class FooterExtension extends AbstractExtension implements GlobalsInterface
{
    private PlaceTypeRepository $placeTypeRepository;

    public function __construct(PlaceTypeRepository $placeTypeRepository)
    {
        $this->placeTypeRepository = $placeTypeRepository;
    }

    public function getGlobals(): array
    {
        return [
            'footer_place_types' => $this->placeTypeRepository->findAll(),
        ];
    }
}
