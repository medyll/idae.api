<?php
	/*
	comparison
	$eq 	Matches values that are equal to a specified value .
	$gt 	Matches values that are greater than a specified value .
	$gte 	Matches values that are greater than or equal to a specified value .
	$in 	Matches any of the values specified in an array.
	$lt 	Matches values that are less than a specified value .
	$lte 	Matches values that are less than or equal to a specified value .
	$ne 	Matches all values that are not equal to a specified value .
	$nin
	*/

	/*
	logicals
	$and 	Joins query clauses with a logical AND returns all documents that match the conditions of both clauses.
	$not 	Inverts the effect of a query expression and returns documents that do not match the query expression.
	$nor 	Joins query clauses with a logical NOR returns all documents that fail to match both clauses.
	$or
	*/

	/*
	Evaluation operaors
	$expr 	Allows use of aggregation expressions within the query language.
	$jsonSchema 	Validate documents against the given JSON Schema.
	$mod 	Performs a modulo operation on the value of a field and selects documents with a specified result.
	$regex 	Selects documents where values match a specified regular expression.
	$text 	Performs text search.
	$where 	Matches documents that satisfy a JavaScript expression.
	*/

	/*
	array query operator
	$all 	Matches arrays that contain all elements specified in the query.
	$elemMatch 	Selects documents if element in the array field matches all the specified $elemMatch conditions.
	$size
	*/

	/*
	element query
    $exists  	Matches documents that have the specified field.
	$type  	Selects documents if a field is of the specified type.
	*/

	namespace Idae\Api;

	use function var_dump;

	class IdaeApiOperators {

		const comparison = ['eq', 'gt', 'gte', 'in', 'lt', 'lte', 'ne', 'nin'];
		const logical    = ['and', 'not', 'nor', 'or'];
		const evaluation = ['expr', 'jsonSchema', 'mod', 'regex', 'text', 'where'];
		const query      = ['all', 'elemMatch', 'size'];
		const element    = ['exists', 'type'];
		const geo        = ['geoIntersects', 'geoWithin', 'near', 'nearSphere',];
		const geometry   = ['box', 'center', 'centerSphere', 'geometry', 'maxDistance', 'minDistance', 'polygon', 'uniqueDoc'];

		const operators
			= [self::comparison,
				self::logical,
				self::evaluation,
				self::query,
				self::geo,
				self::geometry];

		/**
		 * @param $operator
		 *
		 * @return bool
		 */
		public static function is_operator($operator) {

			foreach (self::operators as $index => $operator_list) {
				foreach ($operator_list as $key => $in_operator) {
					if ($operator === $in_operator) return true;
				}
			}

			return false;
		}

	}
