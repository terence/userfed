#setup, update, update composer

## To setup repo/branch from bitbutcket's git

```
> cd /zf_projects/userfed/tool
> ./setup.sh repo branch
```
Code will create in /zf_projects/repo/branch

## To update repo/branch bower's packages

```
> cd /zf_projects/userfed/tool
> ./update-bower.sh repo branch
```

## To update repo/branch git

```
> cd /zf_projects/userfed/tool
> ./update.sh repo branch
```

## To update repo/branch composer's packages

```
> cd /zf_projects/userfed/tool
> ./update-composer.sh repo branch
```

#Common flows
## Setup branch in sub folder (e.g http://userfed.sites.trexanhlab.com/feature-login-and-register-page/ )

### First setup

```
> cd /zf_projects/userfed/tool
> ./setup.sh repo branch
> ./update-bower.sh repo branch
> ./update.sh repo branch
> ./update-database.sh repo branch
```

### Update code

```
> cd /zf_projects/userfed/tool
> ./update.sh repo branch
```

### Update external js, css lib using bower

```
> cd /zf_projects/userfed/tool
> ./update-bower.sh repo branch
```

### Update external php lib using composer

```
> cd /zf_projects/userfed/tool
> ./update-composer.sh repo branch
```

### Update database

```
> ./update-database.sh repo branch
```

## Setup branch in root folder (e.g http://userfed.sites.trexanhlab.com/ )

### First setup

```
> cd /zf_projects/userfed/tool
> ./setup.sh -r repo branch
> ./update-bower.sh -r repo branch
> ./update.sh -r repo branch
```

### Update code

```
> cd /zf_projects/userfed/tool
> ./update.sh -r repo branch
```

### Update external js, css lib using bower

```
> cd /zf_projects/userfed/tool
> ./update-bower.sh -r repo branch
```

### Update external php lib using composer

```
> cd /zf_projects/userfed/tool
> ./update-composer.sh -r repo branch
```

### Remove branch code

```
> cd /zf_projects/userfed/tool
> ./remove.sh -r repo branch
> ./remove.sh repo branch
```


# Cron job
To run script in cron job: need to add execute permission for shell script files.
Whenever git update a file it will reset the permission to without execute.