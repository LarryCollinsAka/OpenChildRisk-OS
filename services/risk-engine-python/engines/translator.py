"""
OpenChildRisk OS — Translation Engine
=======================================
Handles multi-lingual output for all risk alerts
and system messages.

Supported Languages:
    en — English (default)
    fr — Français
    ar — العربية (RTL)
    es — Español (pending)
    pt — Português (pending)
    nl — Nederlands (pending)
    de — Deutsch (pending)

Usage:
    translator = Translator(lang="fr")
    message = translator.get("risk_levels.HIGH")
    # Returns: "ÉLEVÉ"

Fallback Strategy:
    If translation missing → fallback to English
    If English missing → return translation key

Adding New Languages:
    1. Create lang/{code}/alerts.json
    2. Create lang/{code}/system.json
    3. No code changes required

Author: OpenChildRisk OS Team
Version: 1.0.0
"""

import json
import os
import logging
from typing import Optional

logger = logging.getLogger(__name__)

# Path to language files
# Adjust if running outside Docker
LANG_DIR = os.path.join(
    os.path.dirname(__file__),
    "..", "..", "..", "lang"
)

# Supported languages with metadata
SUPPORTED_LANGUAGES = {
    "en": {"name": "English",    "direction": "ltr", "status": "complete"},
    "fr": {"name": "Français",   "direction": "ltr", "status": "complete"},
    "ar": {"name": "العربية",    "direction": "rtl", "status": "complete"},
    "es": {"name": "Español",    "direction": "ltr", "status": "pending"},
    "pt": {"name": "Português",  "direction": "ltr", "status": "pending"},
    "nl": {"name": "Nederlands", "direction": "ltr", "status": "pending"},
    "de": {"name": "Deutsch",    "direction": "ltr", "status": "pending"},
}

# Default language — always English
DEFAULT_LANG = "en"


class Translator:
    """
    Translation engine for OpenChildRisk OS.

    Loads language files at initialization.
    Falls back to English if translation missing.
    Supports variable substitution in messages.

    Example:
        t = Translator("fr")
        msg = t.alert("HIGH", risk_type="choléra",
                      children_at_risk=3800,
                      action="Alertez les ASC")
    """

    def __init__(self, lang: str = DEFAULT_LANG):
        """
        Initialize translator for a specific language.

        Args:
            lang: ISO 639-1 language code (en, fr, ar, es, pt, nl, de)
        """

        # Normalize to lowercase
        self.lang = lang.lower()

        # Validate language code
        if self.lang not in SUPPORTED_LANGUAGES:
            logger.warning(
                f"Language '{lang}' not supported. "
                f"Falling back to English."
            )
            self.lang = DEFAULT_LANG

        # Load translations
        self._translations = self._load(self.lang)
        self._fallback     = self._load(DEFAULT_LANG)

        # Get text direction for this language (ltr/rtl)
        self.direction = SUPPORTED_LANGUAGES[self.lang]["direction"]

        logger.info(
            f"Translator initialized: {self.lang} "
            f"({SUPPORTED_LANGUAGES[self.lang]['name']})"
        )

    def get(self, key: str, **variables) -> str:
        """
        Get a translated string by dot-notation key.

        Looks up key in current language first.
        Falls back to English if not found.
        Substitutes variables using {variable} syntax.

        Args:
            key: Dot-notation key e.g. "risk_levels.HIGH"
            **variables: Variables to substitute in the string

        Returns:
            str: Translated and substituted string

        Example:
            t.get("system.children_at_risk", count=3800)
            # Returns: "3,800 children under 5 at risk"
        """

        # Try current language first
        value = self._lookup(self._translations, key)

        # Fall back to English if not found
        if value is None:
            logger.debug(
                f"Key '{key}' not found in '{self.lang}'. "
                f"Falling back to English."
            )
            value = self._lookup(self._fallback, key)

        # Return key itself if not found anywhere
        if value is None:
            logger.warning(f"Translation key not found: {key}")
            return key

        # Substitute variables
        if variables:
            try:
                value = value.format(**variables)
            except KeyError as e:
                logger.warning(
                    f"Missing variable {e} for key '{key}'"
                )

        return value

    def alert_message(
        self,
        risk_level: str,
        risk_type: str,
        children_at_risk: int,
        action: str,
        district: str = ""
    ) -> str:
        """
        Build complete alert message in target language.

        Args:
            risk_level: HIGH, MEDIUM, or LOW
            risk_type: hazard type code (e.g. "cholera")
            children_at_risk: estimated number of children
            action: recommended action string
            district: district name (optional)

        Returns:
            str: Complete translated alert message
        """

        # Get translated risk type name
        translated_risk_type = self.get(
            f"hazard_types.{risk_type}"
        )

        # Format children count with locale-appropriate separator
        formatted_count = f"{children_at_risk:,}"

        # Get translated action
        translated_action = self.get(
            f"actions.{risk_type}.{risk_level}"
        )

        # Build final message
        return self.get(
            f"alert_messages.{risk_level}",
            risk_type=translated_risk_type,
            children_at_risk=formatted_count,
            action=translated_action,
            district=district,
        )

    def risk_level(self, level: str) -> str:
        """
        Get translated risk level label.

        Args:
            level: HIGH, MEDIUM, LOW, CRITICAL, UNKNOWN

        Returns:
            str: Translated risk level
        """
        return self.get(f"risk_levels.{level}")

    def hazard_name(self, hazard_code: str) -> str:
        """
        Get translated hazard type name.

        Args:
            hazard_code: e.g. "cholera", "heat", "flood"

        Returns:
            str: Translated hazard name
        """
        return self.get(f"hazard_types.{hazard_code}")

    def _load(self, lang: str) -> dict:
        """
        Load all translation files for a language.

        Merges alerts.json and system.json into one dict.

        Args:
            lang: Language code

        Returns:
            dict: Merged translations
        """
        translations = {}
        files = ["alerts", "system"]

        for file_name in files:
            path = os.path.join(
                LANG_DIR, lang, f"{file_name}.json"
            )
            try:
                with open(path, "r", encoding="utf-8") as f:
                    data = json.load(f)
                    # Skip metadata keys
                    for key, value in data.items():
                        if not key.startswith("_"):
                            translations[key] = value
            except FileNotFoundError:
                logger.debug(
                    f"Translation file not found: {path}"
                )
            except json.JSONDecodeError as e:
                logger.error(
                    f"Invalid JSON in {path}: {e}"
                )

        return translations

    def _lookup(
        self,
        translations: dict,
        key: str
    ) -> Optional[str]:
        """
        Look up a value by dot-notation key.

        Args:
            translations: Translation dictionary
            key: Dot-notation key e.g. "risk_levels.HIGH"

        Returns:
            str or None: Found value or None
        """
        parts = key.split(".")
        current = translations

        for part in parts:
            if isinstance(current, dict) and part in current:
                current = current[part]
            else:
                return None

        return current if isinstance(current, str) else None


def get_supported_languages() -> dict:
    """
    Return list of all supported languages with metadata.

    Returns:
        dict: Language codes with name, direction, status
    """
    return SUPPORTED_LANGUAGES