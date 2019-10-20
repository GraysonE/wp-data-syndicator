import AJAX from '../../AJAX.es6.js'
import Success from './Success.es6'
import EnabledPostTypes from './EnabledPostTypes.es6'
import Logs from './Logs.es6'

class SyndicatedPosts {

  constructor () {
    SyndicatedPosts.refresh_view()
  }

  static init () {
    $ = jQuery

    $('.expand_post_details').unbind().click(function () {
      let id = $(this).data('id')
      $('#post-' + id).toggle()
    })

    if (document.getElementById('bulk_data_push')) {
      document.getElementById('bulk_data_push').onclick = function (e) {
        SyndicatedPosts.bulk_push(e)
      }
    }

    if (document.getElementById('refresh_syndicated_posts')) {
      document.getElementById('refresh_syndicated_posts').onclick = function (e) {
        document.querySelector('#syndicated_posts_wrap .loading_spinner').classList.remove('hidden') // SHOW LOADING SPINNER.
        SyndicatedPosts.refresh_view()
      }
    }

  }

  static refresh_view () {

    document.getElementById('syndicated_posts_data').innerHTML = '';

    let data = {}

    AJAX.get(DataSync.api.url + '/source_data').then(function (source_data) {

      data.source_data = source_data
      data.receiver_data = {}
      data.receiver_data.receiver_posts = []
      data.receiver_data.enabled_post_type_site_data = []
      let i = 1
      let connected_site_count = source_data.connected_sites.length

      source_data.connected_sites.forEach((connected_site, index) => {
        AJAX.get(connected_site.url + '/wp-json/data-sync/v1/posts/all', false).then(function (receiver_posts) {
          data.receiver_data.receiver_posts[index] = {}
          data.receiver_data.receiver_posts[index].posts = receiver_posts
          data.receiver_data.receiver_posts[index].site_id = parseInt(connected_site.id)
        })

        AJAX.get(connected_site.url + '/wp-json/data-sync/v1/post_types/check', false).then(function (enabled_post_types) {
          data.receiver_data.enabled_post_type_site_data[index] = {}
          data.receiver_data.enabled_post_type_site_data[index].site_id = connected_site.id
          data.receiver_data.enabled_post_type_site_data[index].enabled_post_types = enabled_post_types

          if (connected_site_count === i) {

            console.log(data)

            let post_count = source_data.posts.length
            let x = 1

            source_data.posts.forEach((post) => {

              data.post_to_get = post;

              AJAX.post_html(DataSync.api.url + '/syndicated_post/' + post.ID, data).then(function (syndicated_post_details) {

                // console.log(syndicated_post_details)

                let result_array = syndicated_post_details.split('null')
                result_array.slice(-1)[0]
                let html = result_array.join(' ')

                // if (1 === x) {
                //   document.getElementById('syndicated_posts_data').innerHTML = html
                // } else {
                $ = jQuery
                $('#syndicated_posts_data').prepend(html)
                // }

                if (post_count === x) {
                  SyndicatedPosts.finish_refresh()
                }

                x++
              })
            })
          }

          i++
        })
      })

    })
  }

  static finish_refresh () {
    document.querySelector('#syndicated_posts_wrap .loading_spinner').classList.add('hidden')
    SyndicatedPosts.init()
    SyndicatedPosts.single_post_actions_init()
  }

  static bulk_push (e) {
    e.preventDefault()

    document.getElementById('syndicated_posts_data').innerHTML = '';
    document.querySelector('#syndicated_posts_wrap .loading_spinner').classList.remove('hidden') // SHOW LOADING SPINNER.

    AJAX.get(DataSync.api.url + '/source_data/bulk_push').then(function (result) {
      SyndicatedPosts.refresh_view()
      Success.show_success_message(result, 'Posts')
      new EnabledPostTypes()
      let logs = new Logs()
      logs.refresh_log()
    })
  }

  static single_post_actions_init () {
    jQuery(function ($) {
      $('.wp_data_synced_post_status_icons .dashicons-editor-unlink').unbind().click(function () {

        let source_post_id = $(this).data('source-post-id')

        SyndicatedPosts.push_single_post_to_all_receivers(source_post_id)

      })

      $('.push_post_now').unbind().click(function (e) {

        e.preventDefault()

        let source_post_id = $(this).data('source-post-id')

        SyndicatedPosts.push_single_post_to_all_receivers(source_post_id)

      })

      $('.overwrite_single_receiver').unbind().click(function (e) {

        e.preventDefault()

        let receiver_site_id = $(this).data('receiver-site-id')
        let source_post_id = $(this).data('source-post-id')

        SyndicatedPosts.push_single_post_to_single_receiver(receiver_site_id, source_post_id)

      })

    })

  }

  static push_single_post_to_all_receivers (source_post_id) {
    document.getElementById('syndicated_posts_wrap').classList.add('hidden') // REMOVE TABLE FOR LOADING.
    document.querySelector('#syndicated_posts .loading_spinner').classList.remove('hidden') // SHOW LOADING SPINNER.

    AJAX.get(DataSync.api.url + '/source_data/overwrite/' + source_post_id).then(function (result) {
      SyndicatedPosts.refresh_view()
      Success.show_success_message(result, 'Post')
      new EnabledPostTypes()
      let logs = new Logs()
      logs.refresh_log()
    })
  }

  static push_single_post_to_single_receiver (receiver_site_id, source_post_id) {
    document.getElementById('syndicated_posts_wrap').classList.add('hidden') // REMOVE TABLE FOR LOADING.
    document.querySelector('#syndicated_posts .loading_spinner').classList.remove('hidden') // SHOW LOADING SPINNER.

    AJAX.get(DataSync.api.url + '/source_data/overwrite/' + source_post_id + '/' + +receiver_site_id).then(function (result) {
      SyndicatedPosts.refresh_view()
      Success.show_success_message(result, 'Post')
      new EnabledPostTypes()
      let logs = new Logs()
      logs.refresh_log()
    })
  }

}

export default SyndicatedPosts