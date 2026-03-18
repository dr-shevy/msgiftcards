<div class="card border-0 shadow-sm mt-3" data-ms2giftcards>
  <div class="card-body">
    <label for="gift_code" class="form-label fw-semibold mb-2">[[+label]]</label>
    <div class="input-group mb-2">
      <input
        type="text"
        name="gift_code"
        id="gift_code"
        value="[[+code]]"
        placeholder="[[+placeholder]]"
        class="form-control"
      />
      <button type="button" class="btn btn-outline-primary" data-ms2giftcards-apply>[[+btn_apply]]</button>
      <button type="button" class="btn btn-outline-secondary d-none" data-ms2giftcards-remove>[[+btn_remove]]</button>
    </div>
    <div class="small mt-2" data-ms2giftcards-message></div>
    <div class="mt-2" data-ms2giftcards-info-block>[[+info]]</div>
  </div>
</div>
