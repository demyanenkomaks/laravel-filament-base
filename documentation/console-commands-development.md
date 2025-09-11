# 🛠️ Команды использующиеся при разработке

### Добавляет проверки кода перед commit

```bash
git config core.hooksPath .git-hooks
```

### Очищает кеш для laravel и filament

```bash
composer cache:clear
```

### Обновляет кеш для laravel и filament

```bash
composer cache
```

### Обновляет и рефакторит PHP-код

```bash
composer rector
```

### Рефакторит стиль кода

```bash
composer pint
```

### Показывает ошибки в коде

```bash
composer phpstan
```

### Запускает команды rector, pint, phpstan

Набор команд который делает проверки и исправления кода. Эти команды выполняются в pre-commit, но только показывает где должны быть исправления.

```bash
composer fix
```
