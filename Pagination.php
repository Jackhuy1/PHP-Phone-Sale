<?php
/**
 * Class Pagination - Xử lý phân trang
 */
class Pagination {
    private $total;
    private $perPage;
    private $currentPage;
    private $baseURL;

    public function __construct($data, $perPage = 10, $baseURL = '') {
        $this->data = $data;
        $this->total = count($data);
        $this->perPage = $perPage;
        $this->baseURL = $baseURL;
        
        // Lấy số trang hiện tại từ URL
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $this->currentPage = max(1, $page);
    }

    public function getTotalPages() {
        return ceil($this->total / $this->perPage);
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getItems() {
        $offset = ($this->currentPage - 1) * $this->perPage;
        return array_slice($this->data, $offset, $this->perPage);
    }

    public function render() {
        echo '<div class="pagination">';
        echo '<div class="pagination-info">';
        echo '<span class="page-count">Tổng: ' . $this->total . ' sản phẩm</span>';
        echo '</div>';
        
        echo '<div class="pagination-controls">';
        $totalPages = $this->getTotalPages();
        $showPages = 5; // Số trang hiển thị
        
        if ($totalPages <= 1) {
            echo '<span class="no-pagination">Không có phân trang</span>';
        } else {
            // Trang trước
            if ($this->currentPage > 1) {
                $prevPage = $this->currentPage - 1;
                echo '<a href="' . $this->baseURL . '?page=' . $prevPage . '" class="prev-btn">❮ Trước</a>';
            } else {
                echo '<span class="no-prev">❮ Trước</span>';
            }
            
            // Trang hiện tại
            echo '<span class="current-page">' . $this->currentPage . '/<span class="total-pages">' . $totalPages . '</span></span>';
            
            // Các trang
            $startPage = max(1, $this->currentPage - floor($showPages / 2));
            $endPage = min($totalPages, $this->currentPage + ceil($showPages / 2));
            
            // Giới hạn trang đầu và cuối
            if ($startPage > 1) {
                echo '<a href="' . $this->baseURL . '?page=1" class="page-link">1</a>';
            }
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                $isActive = ($i === $this->currentPage);
                echo '<a href="' . $this->baseURL . '?page=' . $i . '" class="page-link ' . ($isActive ? 'active' : '') . '">' . $i . '</a>';
            }
            
            // Giới hạn trang cuối
            if ($endPage < $totalPages) {
                echo '<a href="' . $this->baseURL . '?page=' . $totalPages . '" class="page-link">' . $totalPages . '</a>';
            }
            
            // Trang tiếp theo
            if ($this->currentPage < $totalPages) {
                $nextPage = $this->currentPage + 1;
                echo '<a href="' . $this->baseURL . '?page=' . $nextPage . '" class="next-btn">Tiếp ❯</a>';
            } else {
                echo '<span class="no-next">Tiếp ❯</span>';
            }
        }
        
        echo '</div>';
        echo '</div>';
    }
}
