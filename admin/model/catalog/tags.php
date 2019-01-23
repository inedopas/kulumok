<?php
class ModelCatalogTags extends Model {
	public function addTag($data) {
		//$this->event->trigger('pre.admin.tag.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "tag SET status = '" . (int)$data['status'] . "'");

		$tag_id = $this->db->getLastId();

		foreach ($data['tag_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "tag_description SET 
				tag_id = '" . (int)$tag_id . "', 
				language_id = '" . (int)$language_id . "', 
				name = '" . $this->db->escape($value['name']) . "', 
				name_short = '" . $this->db->escape($value['name_short']) . "', 
				description_top = '" . $this->db->escape($value['description_top']) . "',
				description_bottom = '" . $this->db->escape($value['description_bottom']) . "',
				h1 = '" . $this->db->escape($value['h1']) . "', 
				meta_title = '" . $this->db->escape($value['meta_title']) . "', 
				meta_description = '" . $this->db->escape($value['meta_description']) . "', 
				meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['tag_store'])) {
			foreach ($data['tag_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "tag_to_store SET tag_id = '" . (int)$tag_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['tags_seo_url'])) {
			foreach ($data['tags_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'tag_id=" . (int)$tag_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['category_id'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "tag_to_category SET tag_id = '" . (int)$tag_id . "', category_id = '" . $data['category_id'] . "'");
		}

		$this->cache->delete('tag');

		//$this->event->trigger('post.admin.tag.add', $tag_id);

		return $tag_id;
	}

	public function editTag($tag_id, $data) {
		//$this->event->trigger('pre.admin.tag.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "tag SET 
			status = '" . (int)$data['status'] . "'
			WHERE tag_id = '" . (int)$tag_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_description WHERE tag_id = '" . (int)$tag_id . "'");

		foreach ($data['tag_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "tag_description SET 
				tag_id = '" . (int)$tag_id . "', 
				language_id = '" . (int)$language_id . "', 
				name = '" . $this->db->escape($value['name']) . "', 
				name_short = '" . $this->db->escape($value['name_short']) . "', 
				description_top = '" . $this->db->escape($value['description_top']) . "',
				description_bottom = '" . $this->db->escape($value['description_bottom']) . "',
				h1 = '" . $this->db->escape($value['h1']) . "', 
				meta_title = '" . $this->db->escape($value['meta_title']) . "', 
				meta_description = '" . $this->db->escape($value['meta_description']) . "', 
				meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_to_store WHERE tag_id = '" . (int)$tag_id . "'");

		if (isset($data['tag_store'])) {
			foreach ($data['tag_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "tag_to_store SET tag_id = '" . (int)$tag_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'tag_id=" . (int)$tag_id . "'");
		
		if (isset($data['tags_seo_url'])) {
			foreach ($data['tags_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'tag_id=" . (int)$tag_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_to_category WHERE tag_id = '" . (int)$tag_id . "'");
		
		if (isset($data['category_id'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "tag_to_category SET tag_id = '" . (int)$tag_id . "', category_id = '" . $data['category_id'] . "'");
		}

		$this->cache->delete('tag');

		//$this->event->trigger('post.admin.tag.edit', $tag_id);
	}

	public function deleteTags($tag_id) {
		//$this->event->trigger('pre.admin.tag.delete', $tag_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "tag WHERE tag_id = '" . (int)$tag_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_description WHERE tag_id = '" . (int)$tag_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_tag WHERE tag_id = '" . (int)$tag_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_to_store WHERE tag_id = '" . (int)$tag_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'tag_id=" . (int)$tag_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "tag_to_category WHERE tag_id = '" . (int)$tag_id . "'");

		$this->cache->delete('tag');

		//$this->event->trigger('post.admin.tag.delete', $tag_id);
	}

	public function getTag($tag_id) {
		$query = $this->db->query("SELECT DISTINCT *,
			 						(SELECT DISTINCT category_id FROM " . DB_PREFIX . "tag_to_category WHERE tag_id = t.tag_id) AS category_id
			 	FROM " . DB_PREFIX . "tag t 
			 	LEFT JOIN " . DB_PREFIX . "tag_description td ON (t.tag_id = td.tag_id) 
			 	WHERE t.tag_id = '" . (int)$tag_id . "' AND 
			 		td.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getTags($data = array()) {
		$sql = "SELECT *, (select count(*) from " . DB_PREFIX . "product_to_tag where tag_id = t.tag_id)   as count
				FROM " . DB_PREFIX . "tag t 
				LEFT JOIN " . DB_PREFIX . "tag_description td ON (t.tag_id = td.tag_id)  
				WHERE td.language_id = '" . (int)$this->config->get('config_language_id')."'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND td.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND t.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY t.tag_id";

		$sort_data = array(
			'td.name'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY t.tag_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTagDescriptions($tag_id) {
		$tag_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tag_description WHERE tag_id = '" . (int)$tag_id . "'");

		foreach ($query->rows as $result) {
			$tag_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'name_short'             => $result['name_short'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description_top'      => $result['description_top'],
				'description_bottom'      => $result['description_bottom'],
				'h1'				=> $result['h1']
			);
		}

		return $tag_description_data;
	}

	public function getTagStores($tag_id) {
		$tag_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tag_to_store WHERE tag_id = '" . (int)$tag_id . "'");

		foreach ($query->rows as $result) {
			$tag_store_data[] = $result['store_id'];
		}

		return $tag_store_data;
	}

	public function getTotalTags($data = array()) {
		$sql = "SELECT COUNT(DISTINCT t.tag_id) AS total FROM " . DB_PREFIX . "tag t LEFT JOIN " . DB_PREFIX . "tag_description td ON (t.tag_id = td.tag_id)";

		$sql .= " WHERE td.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND td.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND t.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function setSettings($data){ 
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_etopd']."' where `key` = 'newtags_etopd'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_ebottomd']."' where `key` = 'newtags_ebottomd'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_only']."' where `key` = 'newtags_only'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_scategory']."' where `key` = 'newtags_category'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_count']."' where `key` = 'newtags_count'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_related']."' where `key` = 'newtags_related'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_delimiter'])."' where `key` = 'newtags_delimiter'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_name'])."' where `key` = 'newtags_template_name'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_h1'])."' where `key` = 'newtags_template_h1'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_desc1'])."' where `key` = 'newtags_template_desc1'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_desc2'])."' where `key` = 'newtags_template_desc2'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_meta_title'])."' where `key` = 'newtags_template_meta_title'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$this->db->escape($data['setting_template_meta_desc'])."' where `key` = 'newtags_template_meta_desc'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_translit']."' where `key` = 'newtags_translit'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_update']."' where `key` = 'newtags_update'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_autoproducts']."' where `key` = 'newtags_autoproducts'");
		$query = $this->db->query("UPDATE " . DB_PREFIX . "setting set value = '".$data['setting_exclude']."' where `key` = 'newtags_exclude'");
	}

	public function updateTags($data){
		$result = 0;
		if ($data['tag_id']){
			$sql = "select p.product_id 
					from " . DB_PREFIX . "product p
					INNER JOIN " . DB_PREFIX . "product_to_category pc on p.product_id = pc.product_id 
					LEFT JOIN " . DB_PREFIX . "product_attribute pa on p.product_id = pa.product_id 
					LEFT JOIN " . DB_PREFIX . "product_option_value pov on p.product_id = pov.product_id 
					WHERE 1=1 ";
			if ($data['category_id']){
				$sql .= " AND pc.category_id = ".$data['category_id'];
			}

			$attributes = array();
			$attributes_values = array();
			if (isset($data['filtera'])){
				foreach ($data['filtera'] as $key => $item) {
					if ($item != ''){
						if ($data['filterva'][$key] != ''){
							$attributes[] = $item;
							$attributes_values[] = "'".$data['filterva'][$key]."'";
						}
					}
				}
			}
			if ($attributes && $attributes_values){
				$attributes = implode(',', $attributes);
				$attributes_values = implode(',', $attributes_values);
				$sql .= " and pa.attribute_id in ($attributes) and pa.text in ($attributes_values)";
			}

			$options = array();
			$options_values = array();
			if (isset($data['filter'])){
				foreach ($data['filter'] as $key => $item) {
					if ($item != ''){
						if ($data['filterv'][$key] != ''){
							$options[] = $item;
							$options_values[] = $data['filterv'][$key];
						}
					}
				}
			}
			if ($options && $options_values){
				$options = implode(',', $options);
				$options_values = implode(',', $options_values);
				$sql .= " and pov.option_id in ($options) and pov.option_value_id in ($options_values)";
			}

			$sql .= " group by p.product_id";
			
			$query = $this->db->query($sql);
			foreach ($query->rows as $key => $row) {
				$sql = "select * from " . DB_PREFIX . "product_to_tag where product_id = ".$row['product_id']." and tag_id = ".$data['tag_id'];
				$q = $this->db->query($sql);
				if (!$q->num_rows){
					$sql = "insert into " . DB_PREFIX . "product_to_tag set product_id = ".$row['product_id'].", tag_id = ".$data['tag_id'];
					if ($this->db->query($sql))
						$result++;
				}
			}
			
		}

		return $result;
	}
	public function getTagsSeoUrls($tag_id) {
		$tag_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'tag_id=" . (int)$tag_id . "'");

		foreach ($query->rows as $result) {
			$tag_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $tag_seo_url_data;
	}
	public function getTagByName($tag) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "tag_description 
			 	WHERE name = '" . $tag . "'");

		return $query->row;
	}

	public function autoProducts($tag_id, $tag_name){
		$autoproducts = $this->config->get('newtags_autoproducts');
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", ".", "№");
        $tag_name = str_replace($special_chars, '', $tag_name);
		if ($tag_name){
			$sql = "SELECT product_id 
					FROM " . DB_PREFIX . "product_description ";
			if ($autoproducts=='1') {
				$sql .= " WHERE description LIKE '%".$this->db->escape($tag_name)."%' OR name LIKE '%".$this->db->escape($tag_name)."%' ";
			} elseif ($autoproducts=='2') {
				$exclude = $this->config->get('newtags_exclude');
				$exclude_array = explode(',', $exclude);
				$tag_array =  explode(' ', $tag_name);
				$result_array = array_diff($tag_array, $exclude_array);
				$k = 1;
				foreach ($result_array as $result) {
					if ($result){
						if ($k==1) {
							$sql .= " WHERE description LIKE '%".$this->db->escape($result)."%' OR name LIKE '%".$this->db->escape($result)."%' ";
						} else {
							$sql .= " OR description LIKE '%".$this->db->escape($result)."%' OR name LIKE '%".$this->db->escape($result)."%' ";
						}
						$k++;
					}
				}
			}
			$sql .= " GROUP BY product_id";
			$query = $this->db->query($sql);

			foreach ($query->rows as $key => $row) {
				$sql = "select * from " . DB_PREFIX . "product_to_tag where product_id = ".$row['product_id']." and tag_id = ".$tag_id;
				$q = $this->db->query($sql);
				if (!$q->num_rows){
					$sql = "insert into " . DB_PREFIX . "product_to_tag set product_id = ".$row['product_id'].", tag_id = ".$tag_id;
					$this->db->query($sql);
				}
			}
		}		
	}
}
