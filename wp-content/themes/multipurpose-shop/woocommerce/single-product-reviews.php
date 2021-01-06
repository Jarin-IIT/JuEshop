<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="woocommerce-Reviews">
	<div id="comments" style="margin-top:0px;">
		<h4><?php
			if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' && ( $count = $product->get_review_count() ) ) {
				/* translators: 1: reviews count 2: product name */
				printf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'multipurpose-shop' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
			} else {
				__( 'Reviews', 'multipurpose-shop' );
			}
		?></h4>

		<?php if ( have_comments() ) : ?>

			<ol class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', array(
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
					'type'      => 'list',
				) ) );
				echo '</nav>';
			endif; ?>

		<?php else : ?>

			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'multipurpose-shop' ); ?></p>

		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
					$commenter = wp_get_current_commenter();

					$comment_form = array(
					/* translators: string */
						'title_reply'          => have_comments() ? __( 'Add a review', 'multipurpose-shop' ) : sprintf( __( 'Be the first to review &ldquo;%s&rdquo;', 'multipurpose-shop' ), get_the_title() ),
						/* translators: string */
						'title_reply_to'       => __( 'Leave a Reply to %s', 'multipurpose-shop' ),
						'title_reply_before'   => '<div class="section-header comment_reply_header"><h5>',
						'title_reply_after'    => '</h5></div>',
						'comment_notes_after'  => '',
						'fields'               => array(
							
							
							'author' =>'<div class="row"><div class="form-group col-md-6 col-sm-6">' . '<input id="author" placeholder="' . esc_attr__( 'Your Name', 'multipurpose-shop'  ) . '" name="author"  type="text" value="' .
				esc_attr( $commenter['comment_author'] ) . '" size="30" class="form-control" /><span class="required">*</span>
				</div>',
							'email'  => '<div class="form-group col-md-6 col-sm-6">' . '<input id="email" placeholder="' . esc_attr__( 'Your Email', 'multipurpose-shop'  ) . '" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
				'" size="30" class="form-control"   /><span class="required">*</span></div></div>' 
				
				
							
						),
						'label_submit'  => __( 'Submit', 'multipurpose-shop' ),
						'logged_in_as'  => '',
						'comment_field' => '',
						'class_submit'      => 'theme-btn move-eff mt',
						'submit_button' => '<div class="row"><div class="form-group col-md-12">
						<button type="submit"  name="%1$s" id="%2$s" class="%3$s"><span>%4$s</span></button>
						</div></div>'
					);

					if ( $account_page_url = wc_get_page_permalink( 'myaccount' ) ) {
						/* translators: string */
						$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a review.', 'multipurpose-shop' ), esc_url( $account_page_url ) ) . '</p>';
					}

					if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
						$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'multipurpose-shop' ) . '</label><select name="rating" id="rating" aria-required="true" required>
							<option value="">' . esc_html__( 'Rate&hellip;', 'multipurpose-shop' ) . '</option>
							<option value="5">' . esc_html__( 'Perfect', 'multipurpose-shop' ) . '</option>
							<option value="4">' . esc_html__( 'Good', 'multipurpose-shop' ) . '</option>
							<option value="3">' . esc_html__( 'Average', 'multipurpose-shop' ) . '</option>
							<option value="2">' . esc_html__( 'Not that bad', 'multipurpose-shop' ) . '</option>
							<option value="1">' . esc_html__( 'Very poor', 'multipurpose-shop' ) . '</option>
						</select></div>';
					}

					
					 $comment_form['comment_field'] .='<div class="row"><div class="form-group col-md-12 col-sm-12"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"  placeholder="' . esc_attr__( 'Your review', 'multipurpose-shop' ) . '" class="form-control"  >' .
    '</textarea></div></div>';

					comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>

	<?php else : ?>

		<p class="woocommerce-verification-required"><?php esc_html__( 'Only logged in customers who have purchased this product may leave a review.', 'multipurpose-shop' ); ?></p>

	<?php endif; ?>

	<div class="clear"></div>
</div>
