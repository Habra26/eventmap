// Traduction en français des catégories Ticketmaster (utilisées aussi pour les évènements créés par les utilisateurs)
const CATEGORY_LABELS = {
  Music: 'Musique',
  Sports: 'Sports',
  'Arts & Theatre': 'Arts & Théâtre',
  Family: 'Famille',
  Film: 'Cinéma',
  Miscellaneous: 'Autre',
  Undefined: 'Autre',
}

export function categoryLabel(category) {
  if (!category) return null
  return CATEGORY_LABELS[category] ?? category
}
