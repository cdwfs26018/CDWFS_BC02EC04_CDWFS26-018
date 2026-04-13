<?php

namespace App\Enum;

enum LivraisonStatutEnum: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case EN_COURS = 'EN_COURS';
    case LIVREE = 'LIVREE';
}
