<?php

/**
 * Helper Pagination — Pagination dynamique (fonctionnalité bonus)
 * Emplacement : /app/helpers/Pagination.php
 *
 * Classe utilitaire réutilisable dans tous les controllers.
 *
 * Utilisation typique dans un controller :
 *   $pagination = new Pagination($totalItems, $currentPage, $perPage = 10);
 *   $challenges = $model->getChallengesPage($pagination->getOffset(), $pagination->getPerPage());
 *   // Puis dans la vue : $pagination->render()
 */
class Pagination {

    private int $total;       // Nombre total d'éléments
    private int $perPage;     // Éléments par page
    private int $current;     // Page courante
    private int $totalPages;  // Nombre total de pages
    private string $baseUrl;  // URL de base pour les liens

    /**
     * @param int    $total      Nombre total d'éléments dans la base
     * @param int    $current    Page courante (depuis $_GET['page'])
     * @param int    $perPage    Nombre d'éléments par page (défaut 10)
     * @param string $baseUrl    URL de base pour construire les liens (ex: "index.php?action=home")
     */
    public function __construct(int $total, int $current = 1, int $perPage = 10, string $baseUrl = '') {
        $this->total      = max(0, $total);
        $this->perPage    = max(1, $perPage);
        $this->totalPages = max(1, (int)ceil($this->total / $this->perPage));
        $this->current    = max(1, min($current, $this->totalPages));
        $this->baseUrl    = $baseUrl ?: $this->detectBaseUrl();
    }

    // Calcul automatique de l'URL de base à partir du contexte courant
    private function detectBaseUrl(): string {
        $params = $_GET;
        unset($params['page']);
        $query = http_build_query($params);
        return 'index.php' . ($query ? '?' . $query : '');
    }

    // ----------------------------------------------------------------
    // Getters pour le controller
    // ----------------------------------------------------------------

    public function getOffset(): int {
        return ($this->current - 1) * $this->perPage;
    }

    public function getPerPage(): int {
        return $this->perPage;
    }

    public function getCurrentPage(): int {
        return $this->current;
    }

    public function getTotalPages(): int {
        return $this->totalPages;
    }

    public function getTotal(): int {
        return $this->total;
    }

    public function hasPrevious(): bool {
        return $this->current > 1;
    }

    public function hasNext(): bool {
        return $this->current < $this->totalPages;
    }

    // ----------------------------------------------------------------
    // Générer un lien de page
    // ----------------------------------------------------------------
    public function pageUrl(int $page): string {
        $separator = str_contains($this->baseUrl, '?') ? '&' : '?';
        return htmlspecialchars($this->baseUrl . $separator . 'page=' . $page, ENT_QUOTES, 'UTF-8');
    }

    // ----------------------------------------------------------------
    // Rendu HTML Bootstrap 5 — barre de pagination complète
    // ----------------------------------------------------------------
    public function render(): string {
        if ($this->totalPages <= 1) return '';

        $html  = '<nav aria-label="Pagination" class="mt-4">';
        $html .= '<ul class="pagination justify-content-center flex-wrap">';

        // Bouton Précédent
        if ($this->hasPrevious()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->pageUrl($this->current - 1) . '" aria-label="Précédent">';
            $html .= '<span aria-hidden="true">&laquo;</span></a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        }

        // Fenêtre glissante : affiche au maximum 5 numéros de page
        $window = 2;
        $start  = max(1, $this->current - $window);
        $end    = min($this->totalPages, $this->current + $window);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl(1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $this->current) ? ' active" aria-current="page' : '';
            $html  .= '<li class="page-item' . $active . '">';
            $html  .= '<a class="page-link" href="' . $this->pageUrl($i) . '">' . $i . '</a></li>';
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($this->totalPages) . '">' . $this->totalPages . '</a></li>';
        }

        // Bouton Suivant
        if ($this->hasNext()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->pageUrl($this->current + 1) . '" aria-label="Suivant">';
            $html .= '<span aria-hidden="true">&raquo;</span></a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }

        $html .= '</ul>';

        // Résumé discret (ex: "Page 2 sur 7 — 62 résultats")
        $html .= '<p class="text-center text-muted small mt-1">Page ' . $this->current;
        $html .= ' sur ' . $this->totalPages;
        $html .= ' &mdash; ' . $this->total . ' résultat' . ($this->total > 1 ? 's' : '') . '</p>';
        $html .= '</nav>';

        return $html;
    }

    // ----------------------------------------------------------------
    // Méthode statique : lire le numéro de page depuis $_GET['page']
    // ----------------------------------------------------------------
    public static function currentPage(): int {
        return isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    }
}
?> 