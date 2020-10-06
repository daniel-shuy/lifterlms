<?php
/**
 * Tests for LifterLMS Custom Post Types
 *
 * @group LLMS_Roles
 *
 * @since 3.13.0
 * @version [version]
 */
class LLMS_Test_Roles extends LLMS_UnitTestCase {

	/**
	 * Tear down
	 *
	 * @since 3.28.0
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		$wp_roles = wp_roles();
		LLMS_Roles::install();
	}

	/**
	 * test get_all_core_caps() method
	 *
	 * @since 3.13.0
	 *
	 * @return void
	 */
	public function test_get_all_core_caps() {

		$this->assertTrue( is_array( LLMS_Roles::get_all_core_caps() ) );
		$this->assertTrue( ! empty( LLMS_Roles::get_all_core_caps() ) );

	}

	/**
	 * Test get_roles() method.
	 *
	 * @since 3.13.0
	 *
	 * @return void
	 */
	public function test_get_roles() {

		$expect = array(
			'instructor' => __( 'Instructor', 'lifterlms' ),
			'instructors_assistant' => __( 'Instructor\'s Assistant', 'lifterlms' ),
			'lms_manager' => __( 'LMS Manager', 'lifterlms' ),
			'student' => __( 'Student', 'lifterlms' ),
		);
		$this->assertEquals( $expect, LLMS_Roles::get_roles() );

	}

	/**
	 * Test install_roles() method.
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Test for "view_students" on instructors.
	 *
	 * @return  void
	 */
	public function test_install() {

		$wp_roles = wp_roles();

		// Remove first.
		LLMS_Roles::remove_roles();
		add_filter( 'do_print', '__return_true' );
		// Install them.
		LLMS_Roles::install();
		remove_filter( 'do_print', '__return_true' );

		// Ensure all the roles were installed.
		foreach ( array_keys( LLMS_Roles::get_roles() ) as $role ) {
			$this->assertTrue( $wp_roles->is_role( $role ) );
		}

		// Test admin caps were installed.
		$admin = $wp_roles->get_role( 'administrator' );

		foreach ( LLMS_Roles::get_all_core_caps() as $cap ) {
			$this->assertTrue( $admin->has_cap( $cap ) );
		}

		// Test instructor caps.
		$instructor = $wp_roles->get_role( 'instructor' );
		foreach ( LLMS_Roles::get_all_core_caps() as $cap ) {
			$has = $instructor->has_cap( $cap );
			if ( in_array( $cap, array( 'view_lifterlms_reports', 'lifterlms_instructor', 'view_students' ) ) ) {
				$this->assertTrue( $has );
			} else {
				$this->assertFalse( $has );
			}
		}

	}

	/**
	 * Test remove_roles() method.
	 *
	 * @since 3.13.0
	 * @since 3.28.0 Unknown.
	 * @since [version] Make sure only custom roles are removed from the 'adminitrator' role.
	 *
	 * @return void
	 */
	public function test_remove_roles() {

		$wp_roles = wp_roles();

		// Remove them.
		LLMS_Roles::remove_roles();

		// Make sure roles are gone.
		foreach ( array_keys( LLMS_Roles::get_roles() ) as $role ) {
			$this->assertFalse( $wp_roles->is_role( $role ) );
		}

		// Test admin custom caps were removed.
		$admin = $wp_roles->get_role( 'administrator' );
		$admin_caps = LLMS_Unit_Test_Util::call_method( 'LLMS_Roles', 'get_all_caps', array( 'administrator') );
		$wp_caps = $admin_caps['wp'];

		foreach ( $admin_caps as $group => $caps ) {
			foreach ( array_keys( $caps ) as $cap ) {
				if ( 'wp' === $group  ) {
					$this->assertTrue( $admin->has_cap( $cap ) );
				} else {
					$this->assertFalse( $admin->has_cap( $cap ) );
				}
			}
		}

	}

}
