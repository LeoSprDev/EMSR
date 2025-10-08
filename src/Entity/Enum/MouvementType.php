<?php

namespace App\Entity\Enum;

enum MouvementType: string
{
    case ENTREE = 'ENTREE';
    case SORTIE = 'SORTIE';
    case MOBILITE = 'MOBILITE';
    case RENOUVELLEMENT_CDD = 'RENOUVELLEMENT_CDD';

    public function getLabel(): string
    {
        return match($this) {
            self::ENTREE => 'Entrée',
            self::SORTIE => 'Sortie',
            self::MOBILITE => 'Mobilité',
            self::RENOUVELLEMENT_CDD => 'Renouvellement CDD',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ENTREE => 'success',
            self::SORTIE => 'danger',
            self::MOBILITE => 'warning',
            self::RENOUVELLEMENT_CDD => 'info',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::ENTREE => 'fa-plus-circle',
            self::SORTIE => 'fa-minus-circle',
            self::MOBILITE => 'fa-exchange-alt',
            self::RENOUVELLEMENT_CDD => 'fa-redo',
        };
    }

    public static function getChoices(): array
    {
        return [
            'Entrée' => self::ENTREE,
            'Sortie' => self::SORTIE,
            'Mobilité' => self::MOBILITE,
            'Renouvellement CDD' => self::RENOUVELLEMENT_CDD,
        ];
    }

    public static function fromString(string $value): ?self
    {
        return match(strtoupper($value)) {
            'ENTREE' => self::ENTREE,
            'SORTIE' => self::SORTIE,
            'MOBILITE' => self::MOBILITE,
            'RENOUVELLEMENT_CDD' => self::RENOUVELLEMENT_CDD,
            default => null,
        };
    }
}