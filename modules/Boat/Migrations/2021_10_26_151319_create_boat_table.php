<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bravo_boats')) {
            Schema::create('bravo_boats', function (Blueprint $table) {
                $table->bigIncrements('id');

                //Info
                $table->string('title', 255)->nullable();
                $table->string('slug',255)->charset('utf8')->index();
                $table->text('content')->nullable();
                $table->integer('image_id')->nullable();
                $table->integer('banner_image_id')->nullable();
                $table->integer('location_id')->nullable();
                $table->string('address', 255)->nullable();
                $table->string('map_lat',20)->nullable();
                $table->string('map_lng',20)->nullable();
                $table->integer('map_zoom')->nullable();
                $table->tinyInteger('is_featured')->nullable();
                $table->string('gallery', 255)->nullable();
                $table->string('video', 255)->nullable();
                $table->text('faqs')->nullable();

                //Price
                $table->tinyInteger('number')->nullable();
                $table->decimal('price_per_hour', 12,2)->nullable();
                $table->decimal('price_per_day', 12,2)->nullable();
                $table->decimal('min_price', 12,2)->nullable();

                $table->tinyInteger('enable_extra_price')->nullable();
                $table->text('extra_price')->nullable();

                //Extra Info
                $table->integer('max_guest')->nullable();
                $table->integer('cabin')->nullable();
                $table->string('length',255)->nullable();
                $table->string('speed',255)->nullable();
                $table->text('specs')->nullable();

                $table->text('cancel_policy')->nullable();
                $table->text('terms_information')->nullable();

                $table->decimal('review_score',2,1)->nullable();
                $table->string('status',50)->nullable();
                $table->tinyInteger('default_state')->default(1)->nullable();

                $table->tinyInteger('enable_service_fee')->nullable();
                $table->text('service_fee')->nullable();
                $table->integer('min_day_before_booking')->nullable();

                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bravo_boat_translations')) {
            Schema::create('bravo_boat_translations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('origin_id')->unsigned();
                $table->string('locale')->index();

                //Info
                $table->string('title', 255)->nullable();
                $table->text('content')->nullable();
                $table->text('faqs')->nullable();
                $table->text('specs')->nullable();
                $table->text('cancel_policy')->nullable();
                $table->text('terms_information')->nullable();
                $table->string('address', 255)->nullable();

                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bravo_boat_term')) {
            Schema::create('bravo_boat_term', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->integer('term_id')->nullable();
                $table->integer('target_id')->nullable();

                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->timestamps();

            });
        }

        if (!Schema::hasTable('bravo_boat_dates')) {
            Schema::create('bravo_boat_dates', function (Blueprint $table) {

                $table->bigIncrements('id');
                $table->bigInteger('target_id')->nullable();

                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->decimal('price_per_hour', 12,2)->nullable();
                $table->decimal('price_per_day', 12,2)->nullable();
                $table->tinyInteger('number')->nullable();
                $table->tinyInteger('active')->default(0)->nullable();
                $table->text('note_to_customer')->nullable();
                $table->text('note_to_admin')->nullable();
                //$table->tinyInteger('is_instant')->default(0)->nullable();

                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->timestamps();

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bravo_boats');
        Schema::dropIfExists('bravo_boat_translations');
        // Schema::dropIfExists('bravo_boat_term');
        Schema::dropIfExists('bravo_boat_dates');
    }
}
