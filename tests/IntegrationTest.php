<?php

namespace Jaulz\Podium\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Jaulz\Podium\Facades\Podium;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::statement('CREATE EXTENSION IF NOT EXISTS hstore');

    $migration = include __DIR__ . '/../database/migrations/create_podium_extension.php.stub';
    $migration->up();
});

test('increments order', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->text('title');
    });

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order');
    });

    $previousPost = null;
    for ($index = 0; $index < 100; $index++) {
        $post = DB::table('posts')->insertReturning([
            'title' => 'test',
            'order' => 'last',
        ])->first();

        if ($previousPost) {
            expect($post->order)->toBeGreaterThan($previousPost->order);
        }

        $previousPost = $post;
      }
});

test('decrements order', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->text('title');
    });

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order', [], 'first');
    });

    $previousPost = null;
    for ($index = 0; $index < 100; $index++) {
        $post = DB::table('posts')->insertReturning([
            'title' => 'test',
            'order' => 'first',
        ])->first();

        if ($previousPost) {
            expect($previousPost->order)->toBeGreaterThan($post->order);
        }

        $previousPost = $post;
      }
});

test('insert specifically', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->text('title');
    });

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order');
    });

    $firstPost = DB::table('posts')->insertReturning([
        'title' => '1',
        'order' => 'last',
    ])->first();
    $secondPost = DB::table('posts')->insertReturning([
        'title' => '2',
        'order' => 'last',
    ])->first();
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['1', '2']);

    $thirdPost = DB::table('posts')->insertReturning([
        'title' => '3',
        'order' => $secondPost->order // before second post
    ])->first();
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['1', '3', '2']);

    $fourthPost = DB::table('posts')->insertReturning([
        'title' => '4',
        'order' => 'last'
    ])->first();
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['1', '3', '2', '4']);

    $fifthPost = DB::table('posts')->insertReturning([
        'title' => '5',
        'order' => 'first'
    ])->first();
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['5', '1', '3', '2', '4']);

    $sixthPost = DB::table('posts')->insertReturning([
        'title' => '6',
        'order' => $thirdPost->order
    ])->first();
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['5', '1', '6', '3', '2', '4']);

    Podium::rebalance('public', 'posts', 'order', '1');
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual(['5', '1', '6', '3', '2', '4']);
});

test('respects groups', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->integer('category_id');
        $table->text('title');
    });

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order', ['category_id']);
    });

    $firstPost = DB::table('posts')->insertReturning([
        'title' => '1',
        'category_id' => 1,
        'order' => 'last',
    ])->first();
    $secondPost = DB::table('posts')->insertReturning([
        'title' => '2',
        'category_id' => 2,
        'order' => 'last',
    ])->first();
    expect($firstPost->order)->toEqual('0|n');
    expect($firstPost->order)->toEqual($secondPost->order);

    $thirdPost = DB::table('posts')->insertReturning([
        'title' => '3',
        'category_id' => $secondPost->category_id,
        'order' => $secondPost->order // before second post
    ])->first();
    expect($secondPost->order)->toBeGreaterThan($thirdPost->order);
});

test('keeps order after changing buckets', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->text('title');
    });

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order');
    });

    $order = [];
    $count = 100;
    for ($index = 0; $index < $count; $index++) {
        DB::table('posts')->insertReturning([
            'title' => $index,
            'order' => 'last',
        ]);
        $order[] = strval($index);
    }
    expect(DB::table('posts')->count())->toEqual(DB::table('posts')->where(
        'order', 'LIKE', '0|%' 
    )->count());
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual($order);

    Podium::rebalance('public', 'posts', 'order', '1');
    expect(DB::table('posts')->count())->toEqual(DB::table('posts')->where(
        'order', 'LIKE', '1|%' 
    )->count());
    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual($order);
});

test('orders an existant table', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->text('title');
    });

    $order = [];
    $count = 100;
    for ($index = 0; $index < $count; $index++) {
        DB::table('posts')->insertReturning([
            'title' => $index,
        ]);
        $order[] = strval($index);
    }

    Schema::table('posts', function (Blueprint $table) {
        $table->podium('order');
    });

    expect(DB::table('posts')->orderBy('order', 'ASC')->pluck('title')->toArray())->toEqual($order);
});