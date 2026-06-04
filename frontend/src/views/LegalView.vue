<script setup></script>

<template>
  <div class="max-w-3xl mx-auto px-6 py-10">
    <h1 class="text-3xl font-bold text-brand-900 mb-2">Mentions légales & Confidentialité</h1>
    <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : {{ new Date().getFullYear() }}</p>

    <section class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Usage éducatif</h2>
      <p class="text-gray-600 leading-relaxed">
        EventMap est un projet réalisé dans un cadre strictement scolaire et non commercial.
        L'utilisation de l'API Ticketmaster respecte leurs conditions d'utilisation pour les projets
        éducatifs.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Source des données</h2>
      <p class="text-gray-600 leading-relaxed">
        Les évènements affichés proviennent de l'API Ticketmaster. EventMap n'est pas affilié à
        Ticketmaster et ne vend aucun billet directement.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Données personnelles collectées</h2>
      <ul class="text-gray-600 leading-relaxed list-disc list-inside space-y-1">
        <li>Nom</li>
        <li>Adresse email</li>
        <li>Mot de passe (stocké de manière sécurisée et hashée)</li>
      </ul>
      <p class="text-gray-600 leading-relaxed mt-3">
        Aucune donnée n'est partagée avec des tiers. Les données sont conservées jusqu'à la
        suppression du compte, possible à tout moment depuis la page profil.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Cookies et sessions</h2>
      <p class="text-gray-600 leading-relaxed">
        L'application utilise des cookies de session via Laravel Sanctum pour l'authentification.
        Aucun cookie de tracking ou publicitaire n'est utilisé.
      </p>
    </section>

    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-3">RGPD</h2>
      <p class="text-gray-600 leading-relaxed">
        Conformément au RGPD, tu disposes d'un droit d'accès, de rectification et de suppression de
        tes données personnelles. La suppression de ton compte entraîne l'effacement définitif de
        toutes tes données.
      </p>
    </section>
  </div>
</template>

<style scoped></style>
