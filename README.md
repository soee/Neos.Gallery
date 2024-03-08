# Soee Gallery

This package provides a simple implementation of `Gallery` and `Albums` for the Neos CMS.

There are 2 main elements provided by this package:

- `Album` - allows to render a list of images (assets)
- `Gallery` - allows to render a list of albums

## Components

### Album

It is possible to use the `Album` content element, which can be inserted on any page.\
This component has all the same properties as the `Album` document, which means that\
it is possible to render assets by selection, tags and collections.

Assets inside albums can be fetched by:

- manual selection
- tags [1..n]
- asset collections [1..n]

The album items can be sorted (ordered) by file name, title, modification date etc.\
It is also possible to limit the number of items to be fetched and rendered.

### Gallery

@todo

## Pages (document types)

@todo

## Settings

The package provides a few settings, which can be adjusted in the `Settings.yaml` file.

```yaml
Soee:
  Gallery:
    album:
      thumbnail:
        width: 200
        height: 200
        crop: true
      sortBy: 'title'
      sortOrder: 'ASC'
      limit: 10
```

### Integrations

This package does not provide any ready to use integrations with JavaScript libraries to present images.\
It is up do developer to decide which library to use and how to integrate it with the package.\
This section though provides a few examples of how to integrate the package with some of the popular libraries.

#### Fullscreen Lightbox (fslightbox)

Website: [https://fslightbox.com/](https://fslightbox.com/)

The very basic setup will require to include the library in the project and then to use the `data-fslightbox` attribute\
to set images that should be displayed in the lightbox. Since the `Album` single item rendering is done by the `AlbumItem` component,\
it is possible to add the `data-fslightbox` attribute to the `linkAttributes` property. This way all the linked images will have\
this same value for this attribute, and will be displayed in the lightbox as connected group of images (you can click prev/next etc.).

```neosfusion
prototype(Soee.Gallery:Presentation.Fragment.AlbumItem) {
  linkAttributes {
    data-fslightbox = ${String.md5(q(node).property('_identifier'))}
  }
}

