<script type="text/javascript">
	/**
	 * Collect current preprocessing step properties.
	 *
	 * @param {array}  step_nums  List of step numbers to collect.
	 *
	 * @return array
	 */
	function getPreprocessingSteps(step_nums) {
		var $preprocessing = jQuery('#preprocessing'),
			steps = [];

		step_nums.each(function(num) {
			var type = jQuery('[name="preprocessing[' + num + '][type]"]', $preprocessing).val(),
				error_handler = jQuery('[name="preprocessing[' + num + '][on_fail]"]').is(':checked')
					? jQuery('[name="preprocessing[' + num + '][error_handler]"]:checked').val()
					: <?= ZBX_PREPROC_FAIL_DEFAULT ?>,
				params = [];

			var on_fail = {
				error_handler: error_handler,
				error_handler_params: (error_handler == <?= ZBX_PREPROC_FAIL_SET_VALUE ?>
						|| error_handler == <?= ZBX_PREPROC_FAIL_SET_ERROR ?>)
					? jQuery('[name="preprocessing[' + num + '][error_handler_params]"]').val()
					: ''
			};

			if (jQuery('[name="preprocessing[' + num + '][params][0]"]', $preprocessing).length) {
				params.push(jQuery('[name="preprocessing[' + num + '][params][0]"]', $preprocessing).val());
			}
			if (jQuery('[name="preprocessing[' + num + '][params][1]"]', $preprocessing).length) {
				params.push(jQuery('[name="preprocessing[' + num + '][params][1]"]', $preprocessing).val());
			}
			if (jQuery('[name="preprocessing[' + num + '][params][2]"]', $preprocessing).length) {
				// ZBX-16642
				if (type == <?= ZBX_PREPROC_CSV_TO_JSON ?>) {
					if (jQuery('[name="preprocessing[' + num + '][params][2]"]', $preprocessing).is(':checked')) {
						params.push(jQuery('[name="preprocessing[' + num + '][params][2]"]', $preprocessing).val());
					}
					else {
						params.push(0);
					}
				}
				else {
					params.push(jQuery('[name="preprocessing[' + num + '][params][2]"]', $preprocessing).val());
				}
			}

			steps.push(jQuery.extend({
				type: type,
				params: params.join("\n")
			}, on_fail));
		});

		return steps;
	}

	/**
	 * Collect item properties based on it's type.
	 *
	 * @param {string}  form_selector    Form selector.
	 *
	 * @return object
	 */
	function getItemTestProperties(form_selector) {
		var form_data = jQuery(form_selector).serializeJSON(),
			properties = {};

		// Item type specific properties.
		switch (+form_data['type']) {
			case <?= ITEM_TYPE_ZABBIX ?>:
				properties = {
					key: form_data['key'],
					interfaceid: form_data['interfaceid']
				};
				break;

			case <?= ITEM_TYPE_SIMPLE ?>:
				properties = {
					key: form_data['key'],
					interfaceid: form_data['interfaceid'],
					username: form_data['username'],
					password: form_data['password']
				};
				break;

			case <?= ITEM_TYPE_SNMPV1 ?>:
			case <?= ITEM_TYPE_SNMPV2C ?>:
			case <?= ITEM_TYPE_SNMPV3 ?>:
				properties = {
					snmp_oid: form_data['snmp_oid'],
					snmp_community: form_data['snmp_community'],
					interfaceid: form_data['interfaceid']
				};

				if (+form_data['type'] == <?= ITEM_TYPE_SNMPV3 ?>) {
					properties = jQuery.extend(properties, {
						snmpv3_securityname: form_data['snmpv3_securityname'],
						snmpv3_contextname: form_data['snmpv3_contextname'],
						snmpv3_securitylevel: form_data['snmpv3_securitylevel']
					});

					if (properties.snmpv3_securitylevel == <?= ITEM_SNMPV3_SECURITYLEVEL_AUTHPRIV ?>) {
						properties = jQuery.extend(properties, {
							snmpv3_authprotocol: form_data['snmpv3_authprotocol'],
							snmpv3_authpassphrase: form_data['snmpv3_authpassphrase'],
							snmpv3_privprotocol: form_data['snmpv3_privprotocol'],
							snmpv3_privpassphrase: form_data['snmpv3_privpassphrase']
						});
					}
				}
				break;

			case <?= ITEM_TYPE_INTERNAL ?>:
			case <?= ITEM_TYPE_AGGREGATE ?>:
			case <?= ITEM_TYPE_EXTERNAL ?>:
				properties = {
					key: form_data['key']
				};
				break;

			case <?= ITEM_TYPE_DB_MONITOR ?>:
				properties = {
					key: form_data['key'],
					params: form_data['params_ap'],
					username: form_data['username'],
					password: form_data['password']
				};
				break;

			case <?= ITEM_TYPE_HTTPAGENT ?>:
				properties = {
					key: form_data['key'],
					authtype: form_data['http_authtype'],
					follow_redirects: form_data['follow_redirects'] || 0,
					headers: form_data['headers'],
					http_proxy: form_data['http_proxy'],
					output_format: form_data['output_format'] || 0,
					posts: form_data['posts'],
					post_type: form_data['post_type'],
					query_fields: form_data['query_fields'],
					request_method: form_data['request_method'],
					retrieve_mode: form_data['retrieve_mode'],
					ssl_cert_file: form_data['ssl_cert_file'],
					ssl_key_file: form_data['ssl_key_file'],
					ssl_key_password: form_data['ssl_key_password'],
					status_codes: form_data['status_codes'],
					timeout: form_data['timeout'],
					url: form_data['url'],
					verify_host: form_data['verify_host'] || 0,
					verify_peer: form_data['verify_peer'] || 0
				};

				if (properties.authtype != <?= HTTPTEST_AUTH_NONE ?>) {
					properties = jQuery.extend(properties, {
						username: form_data['http_username'],
						password: form_data['http_password']
					});
				}
				break;

			case <?= ITEM_TYPE_IPMI ?>:
				properties = {
					key: form_data['key'],
					ipmi_sensor: form_data['ipmi_sensor'],
					interfaceid: form_data['interfaceid']
				};
				break;

			case <?= ITEM_TYPE_SSH ?>:
				properties = {
					key: form_data['key'],
					authtype: form_data['authtype'],
					params: form_data['params_es'],
					interfaceid: form_data['interfaceid'],
					username: form_data['username'],
					password: form_data['password']
				};

				if (properties.authtype == <?= ITEM_AUTHTYPE_PUBLICKEY ?>) {
					properties = jQuery.extend(properties, {
						publickey: form_data['publickey'],
						privatekey: form_data['privatekey']
					});
				}
				break;

			case <?= ITEM_TYPE_TELNET ?>:
				properties = {
					key: form_data['key'],
					params: form_data['params_es'],
					username: form_data['username'],
					password: form_data['password'],
					interfaceid: form_data['interfaceid']
				};
				break;

			case <?= ITEM_TYPE_JMX ?>:
				properties = {
					key: form_data['key'],
					jmx_endpoint: form_data['jmx_endpoint'],
					username: form_data['username'],
					password: form_data['password']
				};
				break;

			case <?= ITEM_TYPE_CALCULATED ?>:
				properties = {
					key: form_data['key'],
					params: form_data['params_f'],
				};
				break;

			case <?= ITEM_TYPE_SIMPLE ?>:
				properties = {
					key: form_data['key'],
					interfaceid: form_data['interfaceid'],
					username: form_data['username'],
					password: form_data['password'],
				};
				break;
		}

		// Common properties.
		properties = jQuery.extend(properties, {
			delay: form_data['delay'] || '',
			value_type: form_data['value_type'] || <?= CControllerPopupItemTest::ZBX_DEFAULT_VALUE_TYPE ?>,
			item_type: form_data['type'],
			valuemapid: form_data['valuemapid']
		});

		return properties;
	}

	/**
	 * Creates item test modal dialog.
	 *
	 * @param {array}  step_nums          List of step numbers to collect.
	 * @param {bool}   show_final_result  Either the final result should be displayed.
	 * @param {bool}   get_value          Either to show 'get value from host' section.
	 * @param {object} trigger_elmnt      UI element triggered function.
	 * @param {int}    step_obj_nr        Value defines which 'test' button was pressed to open test item dialog:
	 *                                     - 'test' button in edit form footer (-2);
	 *                                     - 'test all' button in preprocessinf tab (-1);
	 *                                     - 'test' button to test single preprocessing step (step index).
	 */
	function openItemTestDialog(step_nums, show_final_result, get_value, trigger_elmnt, step_obj_nr) {
		var $row = jQuery(trigger_elmnt).closest('.preprocessing-list-item, .preprocessing-list-foot, .tfoot-buttons'),
			item_properties = getItemTestProperties('form[name="itemForm"]'),
			cached_values = $row.data('test-data') || [];

		if (typeof item_properties.interfaceid !== 'undefiened' && typeof cached_values.interface !== 'undefined') {
			if (cached_values.interface.interfaceid != item_properties.interfaceid) {
				delete cached_values.interface;
			}
			else {
				delete cached_values.interface.interfaceid2;
			}
		}

		PopUp('popup.itemtest.edit', jQuery.extend(item_properties, {
			steps: getPreprocessingSteps(step_nums),
			hostid: <?= $data['hostid'] ?>,
			test_type: <?= $data['preprocessing_test_type'] ?>,
			step_obj: step_obj_nr,
			show_final_result: show_final_result ? 1 : 0,
			get_value: get_value ? 1 : 0,
			data: cached_values
		}), 'item-test', trigger_elmnt);
	}
</script>
