<?php

/**
 * Description of UserQueryTest
 */
class UserQueryTest extends PHPUnit\Framework\TestCase {

	public function test__construct_whenAllQuery_storesAllParameters() {
		$query = array('get' => 'a');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals('all', $user_query->getGetName());
		$this->assertEquals('all', $user_query->getGetType());
	}

	public function test__construct_whenFavoriteQuery_storesFavoriteParameters() {
		$query = array('get' => 's');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals('favorite', $user_query->getGetName());
		$this->assertEquals('favorite', $user_query->getGetType());
	}

	public function test__construct_whenCategoryQueryAndNoDao_throwsException() {
		$this->expectException(FreshRSS_DAO_Exception::class);
		$this->expectExceptionMessage('Category DAO is not loaded in UserQuery');

		$query = array('get' => 'c_1');
		new FreshRSS_UserQuery($query);
	}

	public function test__construct_whenCategoryQuery_storesCategoryParameters() {
		$category_name = 'some category name';
		$cat = $this->createMock('FreshRSS_Category');
		$cat->expects($this->atLeastOnce())
			->method('name')
			->withAnyParameters()
			->willReturn($category_name);
		$cat_dao = $this->createMock('FreshRSS_CategoryDAO');
		$cat_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn($cat);
		$query = array('get' => 'c_1');
		$user_query = new FreshRSS_UserQuery($query, null, $cat_dao);
		$this->assertEquals($category_name, $user_query->getGetName());
		$this->assertEquals('category', $user_query->getGetType());
	}

	public function test__construct_whenFeedQueryAndNoDao_throwsException() {
		$this->expectException(FreshRSS_DAO_Exception::class);
		$this->expectExceptionMessage('Feed DAO is not loaded in UserQuery');

		$query = array('get' => 'f_1');
		new FreshRSS_UserQuery($query);
	}

	public function test__construct_whenFeedQuery_storesFeedParameters() {
		$feed_name = 'some feed name';
		$feed = $this->createMock('FreshRSS_Feed', array(), array('', false));
		$feed->expects($this->atLeastOnce())
			->method('name')
			->withAnyParameters()
			->willReturn($feed_name);
		$feed_dao = $this->createMock('FreshRSS_FeedDAO');
		$feed_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn($feed);
		$query = array('get' => 'f_1');
		$user_query = new FreshRSS_UserQuery($query, $feed_dao, null);
		$this->assertEquals($feed_name, $user_query->getGetName());
		$this->assertEquals('feed', $user_query->getGetType());
	}

	public function test__construct_whenUnknownQuery_doesStoreParameters() {
		$query = array('get' => 'q');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertNull($user_query->getGetName());
		$this->assertNull($user_query->getGetType());
	}

	public function test__construct_whenName_storesName() {
		$name = 'some name';
		$query = array('name' => $name);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals($name, $user_query->getName());
	}

	public function test__construct_whenOrder_storesOrder() {
		$order = 'some order';
		$query = array('order' => $order);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals($order, $user_query->getOrder());
	}

	public function test__construct_whenState_storesState() {
		$state = 'some state';
		$query = array('state' => $state);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals($state, $user_query->getState());
	}

	public function test__construct_whenUrl_storesUrl() {
		$url = 'some url';
		$query = array('url' => $url);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertEquals($url, $user_query->getUrl());
	}

	public function testToArray_whenNoData_returnsEmptyArray() {
		$user_query = new FreshRSS_UserQuery(array());
		$this->assertIsIterable($user_query->toArray());
		$this->assertCount(0, $user_query->toArray());
	}

	public function testToArray_whenData_returnsArray() {
		$query = array(
			'get' => 's',
			'name' => 'some name',
			'order' => 'some order',
			'search' => 'some search',
			'state' => 'some state',
			'url' => 'some url',
		);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertIsIterable($user_query->toArray());
		$this->assertCount(6, $user_query->toArray());
		$this->assertEquals($query, $user_query->toArray());
	}

	public function testHasSearch_whenSearch_returnsTrue() {
		$query = array(
			'search' => 'some search',
		);
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertTrue($user_query->hasSearch());
	}

	public function testHasSearch_whenNoSearch_returnsFalse() {
		$user_query = new FreshRSS_UserQuery(array());
		$this->assertFalse($user_query->hasSearch());
	}

	public function testHasParameters_whenAllQuery_returnsFalse() {
		$query = array('get' => 'a');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertFalse($user_query->hasParameters());
	}

	public function testHasParameters_whenNoParameter_returnsFalse() {
		$query = array();
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertFalse($user_query->hasParameters());
	}

	public function testHasParameters_whenParameter_returnTrue() {
		$query = array('get' => 's');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertTrue($user_query->hasParameters());
	}

	public function testIsDeprecated_whenCategoryExists_returnFalse() {
		$cat = $this->createMock('FreshRSS_Category');
		$cat_dao = $this->createMock('FreshRSS_CategoryDAO');
		$cat_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn($cat);
		$query = array('get' => 'c_1');
		$user_query = new FreshRSS_UserQuery($query, null, $cat_dao);
		$this->assertFalse($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenCategoryDoesNotExist_returnTrue() {
		$cat_dao = $this->createMock('FreshRSS_CategoryDAO');
		$cat_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn(null);
		$query = array('get' => 'c_1');
		$user_query = new FreshRSS_UserQuery($query, null, $cat_dao);
		$this->assertTrue($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenFeedExists_returnFalse() {
		$feed = $this->createMock('FreshRSS_Feed', array(), array('', false));
		$feed_dao = $this->createMock('FreshRSS_FeedDAO');
		$feed_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn($feed);
		$query = array('get' => 'f_1');
		$user_query = new FreshRSS_UserQuery($query, $feed_dao, null);
		$this->assertFalse($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenFeedDoesNotExist_returnTrue() {
		$feed_dao = $this->createMock('FreshRSS_FeedDAO');
		$feed_dao->expects($this->atLeastOnce())
			->method('searchById')
			->withAnyParameters()
			->willReturn(null);
		$query = array('get' => 'f_1');
		$user_query = new FreshRSS_UserQuery($query, $feed_dao, null);
		$this->assertTrue($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenAllQuery_returnFalse() {
		$query = array('get' => 'a');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertFalse($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenFavoriteQuery_returnFalse() {
		$query = array('get' => 's');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertFalse($user_query->isDeprecated());
	}

	public function testIsDeprecated_whenUnknownQuery_returnFalse() {
		$query = array('get' => 'q');
		$user_query = new FreshRSS_UserQuery($query);
		$this->assertFalse($user_query->isDeprecated());
	}

}
