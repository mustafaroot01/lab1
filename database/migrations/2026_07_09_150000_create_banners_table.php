<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // عنوان البنر أو وصفه
            $table->string('position')->default('home'); // مكان ظهور البنر (دروب داون البنرات: الرئيسية، العروض، إلخ)
            $table->longText('image'); // صورة البنر
            $table->string('link_type')->default('none'); // none, internal_offer, external_url
            $table->string('link_target')->nullable(); // معرف العرض أو الرابط الخارجي
            $table->integer('sort_order')->default(1); // ترتيب الظهور
            $table->boolean('is_active')->default(true); // مفعل أو موقوف
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
