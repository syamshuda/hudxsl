<?php
$page_title = "FAQ (Tanya Jawab)";
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-4">Frequently Asked Questions (FAQ)</h1>
            <p class="lead text-center mb-5">
                Temukan jawaban untuk pertanyaan yang paling sering diajukan mengenai layanan kami.
            </p>

            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Bagaimana cara memesan produk di Sabaku ID?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Anda dapat memesan dengan cara memilih produk yang Anda inginkan, menambahkannya ke keranjang, lalu melanjutkan ke halaman checkout. Ikuti instruksi di halaman checkout untuk mengisi alamat dan memilih metode pembayaran.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Metode pembayaran apa saja yang tersedia?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Saat ini kami menerima pembayaran melalui Transfer Bank dan QRIS. Untuk beberapa produk fisik, kami juga menyediakan opsi Bayar di Tempat (Cash on Delivery / COD) jika Anda memenuhi syarat.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Berapa lama waktu pengiriman?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Waktu pengiriman bervariasi tergantung lokasi Anda dan kurir yang dipilih. Estimasi waktu pengiriman akan ditampilkan saat Anda memilih opsi pengiriman di halaman checkout.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Apakah saya bisa menjadi penjual di Sabaku ID?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Tentu! Platform ini ditujukan untuk siswa SMKN 1 Bangkalan. Anda dapat mendaftar sebagai "Penjual" saat registrasi akun dan mengikuti proses verifikasi yang kami sediakan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>