<?php

	namespace IdaDataShemeField;

	class IdaeDataShemeHasField {

		private $schemeName;

		private  $fieldCode;
		private  $fieldName;
		private  $fieldCodeRaw;
		private  $fieldNameRaw;
		private  $fieldIcon;
		private  $fieldOrder;

		/**
		 * IdaDataShemeHasField constructor.
		 *
		 * @param $schemeName
		 */
		public function __construct($schemeName) { $this->schemeName = $schemeName; }

		/**
		 * @return mixed
		 */
		public function getFieldCode() {
			return $this->fieldCode;
		}

		/**
		 * @param mixed $fieldCode
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldCode($fieldCode) {
			$this->fieldCode = $fieldCode;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getFieldName() {
			return $this->fieldName;
		}

		/**
		 * @param mixed $fieldName
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldName($fieldName) {
			$this->fieldName = $fieldName;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getFieldCodeRaw() {
			return $this->fieldCodeRaw;
		}

		/**
		 * @param mixed $fieldCodeRaw
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldCodeRaw($fieldCodeRaw) {
			$this->fieldCodeRaw = $fieldCodeRaw;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getFieldNameRaw() {
			return $this->fieldNameRaw;
		}

		/**
		 * @param mixed $fieldNameRaw
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldNameRaw($fieldNameRaw) {
			$this->fieldNameRaw = $fieldNameRaw;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getFieldIcon() {
			return $this->fieldIcon;
		}

		/**
		 * @param mixed $fieldIcon
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldIcon($fieldIcon) {
			$this->fieldIcon = $fieldIcon;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getFieldOrder() {
			return $this->fieldOrder;
		}

		/**
		 * @param mixed $fieldOrder
		 *
		 * @return IdaeDataShemeHasField
		 */
		public function setFieldOrder($fieldOrder) {
			$this->fieldOrder = $fieldOrder;

			return $this;
		}
	}
