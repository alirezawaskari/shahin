<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "نظرات کاربران" — the star rating + comment a visitor leaves on a branch page or on a
 * partner's profile, and the source of the "میانگین امتیاز" tile on the about-us page.
 *
 * The existing `reviews` table cannot be used: it is product copywriting (product_id +
 * type 0/1 for strengths/weaknesses, written by staff), with no score, no author and no
 * moderation state.
 *
 * Morphable because the branch frontend and the partner-profile frontend want the same
 * thing about different subjects (Center, Employee) — one table, one moderation screen.
 *
 * Ratings arrive from the public API, so nothing is visible until an admin approves it:
 * `status` defaults to PENDING and the frontend only ever reads APPROVED.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->morphs('rateable');
            // Set when the reviewer was logged in; a guest review keeps only name/family.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 110)->nullable();
            $table->string('family', 110)->nullable();
            $table->text('text')->nullable();
            $table->unsignedTinyInteger('score')->default(5);
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            // the frontend's only query: approved ratings of one subject, newest first
            $table->index(['rateable_type', 'rateable_id', 'status'], 'ratings_subject_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
