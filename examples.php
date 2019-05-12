<?php

/* select example */
/* queries a select from the 'answers' table */
$Answers = Query::select([
    'table' => 'answers',
    'columns' => ['id', 'name'],
    'where' => 'quiz_id = :quiz_id AND question_num = :question_num',
    'order_by' => 'text ASC',
    'limit' => '4',
    'data' => [
        'quiz_id' => 6,
        'question_num' => 3
    ]
]);


/* insert example */
/* inserts a row into the 'quizzes' table */
/* data corresponds to the columns and values */
$LastId = Query::insert([
    'table' => 'quizzes',
    'data' => [
        'name' => 'US President ellection 2019',
        'description' => 'Who will be the next president?'
    ]
]);

/* updates a table */
/* Query::update($columnsAndValuesToUpdate, $params) */
/* $params ['table', 'where', 'order_by', 'limit', 'data'] */
Query::update([
    'name' => 'US President ellection 2018'
], [
    'table' => 'quizzes',
    'where' => 'id = :id',
    'data' => [
        'id' => 7
    ]
]);


/* delete example */
/* deletes from the 'quizzes' table */
Query::delete([
    'table' => 'quizzes',
    'where' => 'id = :id',
    'data' => [
        'id' => 10
    ]
]);