# 🛠️ Команды использующиеся при разработке

## Меню

- [Качество кода](#качество-кода)
  - [Git hooks](#git-hooks)
  - [Кеш](#кеш)
  - [Проверки и рефакторинг](#проверки-и-рефакторинг)
- [Тестирование архитектуры](#тестирование-архитектуры)
- [Модули (coolsam/modules)](#модули-coolsammodules)
  - [Управление модулями](#управление-модулями)
  - [Модель и связанные файлы](#модель-и-связанные-файлы)
  - [Filament Resource](#filament-resource)
  - [API Resource](#api-resource)
  - [Миграции и сиды](#миграции-и-сиды)

---

## Качество кода

### Git hooks

Добавляет проверки кода перед commit.

```bash
git config core.hooksPath .git-hooks
```

### Кеш

```bash
composer cache:clear  # Очистить кеш Laravel и Filament
composer cache        # Обновить кеш Laravel и Filament
```

### Проверки и рефакторинг

```bash
composer rector   # Рефакторинг PHP-кода
composer pint     # Фиксация стиля кода
composer phpstan  # Статический анализ
composer fix      # Полный набор проверок (test:arch, rector, pint, phpstan)
```

`composer fix` выполняется в pre-commit и только показывает, где нужны исправления.

---

## Тестирование архитектуры

Проверяет соблюдение архитектурных правил: расположение классов (Models, Controllers, API Resources и др.), отсутствие отладочных функций, security preset Pest.

Правила описаны в `tests/Architecture/ArchTest.php`.

```bash
composer test:arch  # Запуск архитектурных тестов Pest
```

Команда также входит в `composer fix` (выполняется первой).

---

## Модули (coolsam/modules)

Документация пакета: [laravelmodules.com](https://laravelmodules.com/docs/12/getting-started/introduction) · [artisan-команды](https://laravelmodules.com/docs/12/advanced/artisan-commands)

### Управление модулями

```bash
php artisan module:list                  # Список модулей
php artisan module:make {Module}         # Создать модуль
php artisan module:make {Module} --api   # Создать API-модуль
php artisan module:enable {Module}       # Включить модуль
php artisan module:disable {Module}      # Отключить модуль
php artisan module:delete {Module}       # Удалить модуль
```

### Модель и связанные файлы

**Одной командой** — модель, миграция, фабрика, сидер, контроллер:

```bash
php artisan module:make-model {Name} {Module} -m -f -s -c
```

| Флаг | Что создаёт |
|------|-------------|
| `-m` | миграция |
| `-f` | фабрика |
| `-s` | сидер |
| `-c` | контроллер |

**По отдельности:**

```bash
php artisan module:make-model {Name} {Module}                              # Модель
php artisan module:make-migration create_{table}_table {Module}              # Миграция
php artisan module:make-factory {Name} {Module}                              # Фабрика
php artisan module:make-seed {Name}Seeder {Module}                         # Сидер
php artisan module:make-controller {Name}Controller {Module}               # REST-контроллер
php artisan module:make-controller {Name}Controller {Module} --api           # API-контроллер (без create/edit)
```

### Filament Resource

```bash
php artisan module:make:filament-resource {Name} {Module}                    # Ресурс Filament для модели
php artisan module:make:filament-resource {Name} {Module} --simple           # Простой ресурс (одна страница, модальные окна)
php artisan module:make:filament-resource {Name} {Module} --generate         # Сгенерировать форму и колонки таблицы из БД
```

Алиасы: `module:filament:resource`, `module:filament:make-resource`.

```bash
php artisan module:make:filament-page {Name} {Module}      # Страница Filament
php artisan module:make:filament-widget {Name} {Module}  # Виджет Filament
php artisan module:make:filament-cluster {Name} {Module}   # Кластер Filament
```

### API Resource

Laravel JsonResource для API. Создаётся в `Modules/{Module}/app/Transformers/`.

```bash
php artisan module:make-resource {Name}Resource {Module}              # API Resource
php artisan module:make-resource {Name}Resource {Module} --collection  # Resource Collection
```

При генерации модели с флагом `-R` / `--resource`:

```bash
php artisan module:make-model {Name} {Module} -R  # Модель + API Resource
```

### Миграции и сиды

```bash
php artisan module:migrate                                              # Миграции всех модулей
php artisan module:migrate {Module}                                     # Миграции конкретного модуля
php artisan module:migrate-rollback                                     # Откат последней миграции модулей
php artisan module:seed                                                 # Сиды всех модулей
php artisan module:seed {Module} --class={Name}DatabaseSeeder           # Сид конкретного модуля
```
