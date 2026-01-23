<?php

/**
 * 派单系统API客户端
 * 用于调用ThinkPHP6开发的派单系统API
 */
class PaidanApiClient extends CApplicationComponent
{
	/**
	 * API基础URL
	 */
	public $apiBaseUrl = '';

	/**
	 * API认证Token
	 */
	public $apiToken = '';

	/**
	 * 请求超时时间（秒）
	 * 对于数据量大的城市，派单系统可能需要较长处理时间
	 */
	public $timeout = 1800;  // 增加到 1800 秒（30分钟）

	/**
	 * 连接超时时间（秒）
	 */
	public $connectTimeout = 300;  // 连接建立超时 30 秒

	/**
	 * 调用派单系统API
	 * @param string $endpoint API端点
	 * @param array $params 请求参数
	 * @return array 返回数据
	 */
	public function callApi($endpoint, $params = array()) {
		$url = rtrim($this->apiBaseUrl, '/') . '/' . ltrim($endpoint, '/');

	// 添加认证Token
	if (!empty($this->apiToken)) {
		$params['token'] = $this->apiToken;
	}

	// 🔧 如果 filter_params 是 JSON 字符串，解析并平铺到顶层参数
	if (isset($params['filter_params']) && is_string($params['filter_params'])) {
		$filterParams = json_decode($params['filter_params'], true);
		if (is_array($filterParams)) {
			// 移除 filter_params，将其内容平铺到顶层
			unset($params['filter_params']);
			$params = array_merge($params, $filterParams);
		}
	}

	// 将数组参数转换为逗号分隔的字符串（派单系统API期望的格式）
	$processedParams = array();
	foreach ($params as $key => $value) {
		if (is_array($value)) {
			// 数组转为逗号分隔的字符串
			$processedParams[$key] = implode(',', $value);
		} else {
			$processedParams[$key] = $value;
		}
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($processedParams));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);  // 连接超时
	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);  // 总超时时间
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	// 设置 Content-Type 为 application/x-www-form-urlencoded（ThinkPHP 需要）
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'
	));

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

	// 记录调试日志
	Yii::log('派单API调用: ' . $url . "\n参数: " . json_encode($processedParams) . "\nPOST字符串: " . http_build_query($processedParams) . "\nHTTP状态: " . $httpCode . "\n响应: " . substr($response, 0, 500), 'info', 'PaidanApi');

		if ($error) {
			throw new Exception('【派单API错误】连接失败: ' . $error);
		}

		if ($httpCode != 200) {
			// 尝试解析错误响应
			$errorMsg = '【派单API错误】HTTP状态码: ' . $httpCode;
			$errorData = json_decode($response, true);
			if ($errorData && isset($errorData['message'])) {
				$errorMsg .= '，错误信息: ' . $errorData['message'];
			} else {
				$errorMsg .= '，响应: ' . substr($response, 0, 200);
			}
			throw new Exception($errorMsg);
		}

		$data = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception('【派单API错误】返回数据格式错误: ' . json_last_error_msg() . '，原始响应: ' . substr($response, 0, 200));
		}

		return $data;
	}

	/**
	 * 获取客户数据
	 * @param array $params 筛选参数
	 * @return array 客户数据列表
	 */
	public function fetchCustomers($params) {
		$endpoint = 'api/data/customer'; // ThinkPHP6路由：app/data/controller/ApiCustomer
		$result = $this->callApi($endpoint, $params);

		// 兼容两种返回格式：status 和 code
		$isSuccess = false;
		if (isset($result['status']) && $result['status'] == 1) {
			$isSuccess = true;
		} elseif (isset($result['code']) && $result['code'] == 200) {
			$isSuccess = true;
		}

		if ($isSuccess) {
			// 处理行数据格式
			$rows = isset($result['data']['rows']) ? $result['data']['rows'] : array();

			// 如果行数据包含 row_index 和 data 结构，则提取 data
			$processedRows = array();
			foreach ($rows as $row) {
				if (isset($row['data']) && is_array($row['data'])) {
					$processedRows[] = $row['data'];
				} else {
					$processedRows[] = $row;
				}
			}

			return array(
				'status' => 1,
				'message' => isset($result['message']) ? $result['message'] : 'success',
				'data' => array(
					'headers' => isset($result['data']['headers']) ? $result['data']['headers'] : array(),
					'rows' => $processedRows,
					'total_count' => isset($result['data']['total_count']) ? $result['data']['total_count'] : 0,
				)
			);
		} else {
			// 提取派单系统返回的错误信息
			$errorMsg = '获取客户数据失败';
			if (isset($result['message'])) {
				$errorMsg = $result['message'];
			} elseif (isset($result['msg'])) {
				$errorMsg = $result['msg'];
			}

			// 添加更多错误详情
			if (isset($result['code'])) {
				$errorMsg = '【派单系统返回错误 ' . $result['code'] . '】' . $errorMsg;
			}

			return array(
				'status' => 0,
				'message' => $errorMsg,
				'data' => array(
					'headers' => array(),
					'rows' => array(),
					'total_count' => 0,
				),
				'raw_error' => $result // 保留原始错误信息用于调试
			);
		}
	}

	/**
	 * 获取门店数据
	 * @param array $params 筛选参数
	 * @return array 门店数据列表
	 */
	public function fetchStores($params) {
		$endpoint = 'api/data/shop'; // ThinkPHP6路由：app/data/controller/ApiShop
		$result = $this->callApi($endpoint, $params);

		// 兼容两种返回格式：status 和 code
		$isSuccess = false;
		if (isset($result['status']) && $result['status'] == 1) {
			$isSuccess = true;
		} elseif (isset($result['code']) && $result['code'] == 200) {
			$isSuccess = true;
		}

		if ($isSuccess) {
			// 处理行数据格式
			$rows = isset($result['data']['rows']) ? $result['data']['rows'] : array();

			// 如果行数据包含 row_index 和 data 结构，则提取 data
			$processedRows = array();
			foreach ($rows as $row) {
				if (isset($row['data']) && is_array($row['data'])) {
					$processedRows[] = $row['data'];
				} else {
					$processedRows[] = $row;
				}
			}

			return array(
				'status' => 1,
				'message' => isset($result['message']) ? $result['message'] : 'success',
				'data' => array(
					'headers' => isset($result['data']['headers']) ? $result['data']['headers'] : array(),
					'rows' => $processedRows,
					'total_count' => isset($result['data']['total_count']) ? $result['data']['total_count'] : 0,
				)
			);
		} else {
			// 提取派单系统返回的错误信息
			$errorMsg = '获取门店数据失败';
			if (isset($result['message'])) {
				$errorMsg = $result['message'];
			} elseif (isset($result['msg'])) {
				$errorMsg = $result['msg'];
			}

			// 添加更多错误详情
			if (isset($result['code'])) {
				$errorMsg = '【派单系统返回错误 ' . $result['code'] . '】' . $errorMsg;
			}

			return array(
				'status' => 0,
				'message' => $errorMsg,
				'data' => array(
					'headers' => array(),
					'rows' => array(),
					'total_count' => 0,
				),
				'raw_error' => $result
			);
		}
	}

	/**
	 * 获取主合约数据
	 * @param array $params 筛选参数
	 * @return array 主合约数据列表
	 */
	public function fetchContracts($params) {
		$endpoint = 'api/data/contract'; // ThinkPHP6路由：app/data/controller/ApiContract
		$result = $this->callApi($endpoint, $params);

		// 兼容两种返回格式：status 和 code
		$isSuccess = false;
		if (isset($result['status']) && $result['status'] == 1) {
			$isSuccess = true;
		} elseif (isset($result['code']) && $result['code'] == 200) {
			$isSuccess = true;
		}

		if ($isSuccess) {
			// 处理行数据格式
			$rows = isset($result['data']['rows']) ? $result['data']['rows'] : array();

			// 如果行数据包含 row_index 和 data 结构，则提取 data
			$processedRows = array();
			foreach ($rows as $row) {
				if (isset($row['data']) && is_array($row['data'])) {
					$processedRows[] = $row['data'];
				} else {
					$processedRows[] = $row;
				}
			}

			return array(
				'status' => 1,
				'message' => isset($result['message']) ? $result['message'] : 'success',
				'data' => array(
					'headers' => isset($result['data']['headers']) ? $result['data']['headers'] : array(),
					'rows' => $processedRows,
					'total_count' => isset($result['data']['total_count']) ? $result['data']['total_count'] : 0,
				)
			);
		} else {
			// 提取派单系统返回的错误信息
			$errorMsg = '获取主合约数据失败';
			if (isset($result['message'])) {
				$errorMsg = $result['message'];
			} elseif (isset($result['msg'])) {
				$errorMsg = $result['msg'];
			}

			// 添加更多错误详情
			if (isset($result['code'])) {
				$errorMsg = '【派单系统返回错误 ' . $result['code'] . '】' . $errorMsg;
			}

			return array(
				'status' => 0,
				'message' => $errorMsg,
				'data' => array(
					'headers' => array(),
					'rows' => array(),
					'total_count' => 0,
				),
				'raw_error' => $result
			);
		}
	}

	/**
	 * 获取虚拟合约数据
	 * @param array $params 筛选参数
	 * @return array 虚拟合约数据列表
	 */
	public function fetchVirtualContracts($params) {
		$endpoint = 'api/data/virtual'; // ThinkPHP6路由：app/data/controller/ApiVirtual
		$result = $this->callApi($endpoint, $params);

		// 兼容两种返回格式：status 和 code
		$isSuccess = false;
		if (isset($result['status']) && $result['status'] == 1) {
			$isSuccess = true;
		} elseif (isset($result['code']) && $result['code'] == 200) {
			$isSuccess = true;
		}

		if ($isSuccess) {
			// 处理行数据格式
			$rows = isset($result['data']['rows']) ? $result['data']['rows'] : array();

			// 如果行数据包含 row_index 和 data 结构，则提取 data
			$processedRows = array();
			foreach ($rows as $row) {
				if (isset($row['data']) && is_array($row['data'])) {
					$processedRows[] = $row['data'];
				} else {
					$processedRows[] = $row;
				}
			}

			return array(
				'status' => 1,
				'message' => isset($result['message']) ? $result['message'] : 'success',
				'data' => array(
					'headers' => isset($result['data']['headers']) ? $result['data']['headers'] : array(),
					'rows' => $processedRows,
					'total_count' => isset($result['data']['total_count']) ? $result['data']['total_count'] : 0,
				)
			);
		} else {
			// 提取派单系统返回的错误信息
			$errorMsg = '获取虚拟合约数据失败';
			if (isset($result['message'])) {
				$errorMsg = $result['message'];
			} elseif (isset($result['msg'])) {
				$errorMsg = $result['msg'];
			}

			// 添加更多错误详情
			if (isset($result['code'])) {
				$errorMsg = '【派单系统返回错误 ' . $result['code'] . '】' . $errorMsg;
			}

			return array(
				'status' => 0,
				'message' => $errorMsg,
				'data' => array(
					'headers' => array(),
					'rows' => array(),
					'total_count' => 0,
				),
				'raw_error' => $result
			);
		}
	}
}

