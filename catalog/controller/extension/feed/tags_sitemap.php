<?php class ControllerExtensionFeedTagsSitemap extends Controller {
	private $from_charset = 'utf-8';
	public function index() {
		if ($this->config->get('feed_tags_sitemap_status')) {

			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			$this->load->model('catalog/tags');
            $this->load->model('catalog/category');

			$total =  $this->model_catalog_tags->getTotalTags();
			$limit = $this->config->get('feed_tags_sitemap_limit') ? $this->config->get('feed_tags_sitemap_limit') : 500;
			$parts = ceil($total / $limit) - 1;

			$filter = array(
				'start' => ($this->request->get['part'] && $parts >= $this->request->get['part'] )? $this->request->get['part'] * $limit : 0,
				'limit' => $limit
			);
			
            $tags = $this->model_catalog_tags->getTags($filter);

                foreach ($tags as $tag) {
                    $href = $this->url->link('product/tags', 'tag_id=' . $tag['tag_id']);
                    if ($tag['category_id']){
                        $path = $tag['category_id'];
                        $flag = false;
                        $tid = $tag['category_id'];
                        while (!$flag) {
                            $c = $this->model_catalog_category->getCategory($tid);
                            if ($c['parent_id']){
                                $path = $c['parent_id']."_".$path;
                                $tid = $c['parent_id'];
                            }
                            else{
                                $flag = true;
                            }
                        }
                        $href = $this->url->link('product/tags', 'path='.$path.'&tag_id=' . $tag['tag_id']);
                    }
                    $output .= '<url>';
                    $output .= '<loc>' . $href . '</loc>';
                    $output .= '<changefreq>weekly</changefreq>';
                    $output .= '<priority>0.5</priority>';
                    $output .= '</url>';
                }

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

}